<?php

namespace App\Http\Controllers;

use App\Models\MesImportBatch;
use App\Models\MesProduction;
use App\Services\MesImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MesProductionController extends Controller
{
    public function __construct(
        private readonly MesImportService $importService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'machine' => ['nullable', 'string', 'max:50'],
            'shift' => ['nullable', 'string', 'max:10'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $startDate = $filters['start_date']
            ?? now()->startOfMonth()->toDateString();

        $endDate = $filters['end_date']
            ?? now()->endOfMonth()->toDateString();

        $query = MesProduction::query()
            ->active()
            ->whereBetween('datprd', [$startDate, $endDate]);

        if (!empty($filters['machine'])) {
            $query->where('mcnnam', $filters['machine']);
        }

        if (!empty($filters['shift'])) {
            $query->where('shft', $filters['shift']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('prpno', 'ILIKE', "%{$search}%")
                    ->orWhere('custnam', 'ILIKE', "%{$search}%");
            });
        }

        $productions = $query
            ->orderByDesc('datprd')
            ->orderBy('shft')
            ->orderBy('mcnnam')
            ->paginate(25)
            ->withQueryString();

        $availableMachines = MesProduction::query()
            ->active()
            ->whereNotNull('mcnnam')
            ->distinct()
            ->orderBy('mcnnam')
            ->pluck('mcnnam');

        $availableShifts = MesProduction::query()
            ->active()
            ->whereNotNull('shft')
            ->distinct()
            ->orderBy('shft')
            ->pluck('shft');

        $latestDate = MesProduction::query()
            ->active()
            ->max('datprd');

        return view('actual-production.index', [
            'productions' => $productions,
            'availableMachines' => $availableMachines,
            'availableShifts' => $availableShifts,
            'latestDate' => $latestDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function importForm(): View
    {
        $batches = MesImportBatch::query()
            ->active()
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('actual-production.import', compact('batches'));
    }

    public function importPreview(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:51200',
            ],
            'import_type' => [
                'required',
                'in:DAILY,CUMULATIVE',
            ],
        ]);

        $file = $request->file('excel_file');

        if (!$file) {
            return redirect()
                ->route('mes.import')
                ->with('error', 'File Excel tidak ditemukan.');
        }

        $originalName = basename($file->getClientOriginalName());

        $storedPath = $file->store('imports/mes', 'local');

        if (!$storedPath) {
            return redirect()
                ->route('mes.import')
                ->with('error', 'File gagal disimpan. Silakan coba kembali.');
        }

        $storage = Storage::disk('local');

        try {
            $preview = $this->importService->parseExcel(
                $storage->path($storedPath),
                $originalName
            );

            if (isset($preview['error'])) {
                $storage->delete($storedPath);

                return redirect()
                    ->route('mes.import')
                    ->with('error', $preview['error']);
            }
        } catch (Throwable $exception) {
            report($exception);

            $storage->delete($storedPath);

            return redirect()
                ->route('mes.import')
                ->with(
                    'error',
                    'File Excel gagal dibaca. Periksa format dan struktur kolomnya.'
                );
        }

        session([
            'mes_import_temp' => $storedPath,
            'mes_import_original_name' => $originalName,
            'mes_import_type' => $validated['import_type'],
        ]);

        return view('actual-production.import_preview', compact('preview'));
    }

    public function importConfirm(Request $request): RedirectResponse
    {
        $tempPath = session('mes_import_temp');
        $originalName = session('mes_import_original_name');
        $importType = session('mes_import_type');

        if (!$tempPath || !$originalName || !$importType) {
            return redirect()
                ->route('mes.import')
                ->with(
                    'error',
                    'Sesi import telah kedaluwarsa. Silakan upload ulang file.'
                );
        }

        if (!in_array($importType, ['DAILY', 'CUMULATIVE'], true)) {
            return redirect()
                ->route('mes.import')
                ->with('error', 'Tipe import tidak valid.');
        }

        $storage = Storage::disk('local');

        if (!$storage->exists($tempPath)) {
            session()->forget([
                'mes_import_temp',
                'mes_import_original_name',
                'mes_import_type',
            ]);

            return redirect()
                ->route('mes.import')
                ->with(
                    'error',
                    'File sementara tidak ditemukan. Silakan upload ulang.'
                );
        }

        try {
            $preview = $this->importService->parseExcel(
                $storage->path($tempPath),
                $originalName
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('mes.import')
                ->with(
                    'error',
                    'File gagal diperiksa ulang. Silakan upload ulang.'
                );
        }

        if (isset($preview['error'])) {
            return redirect()
                ->route('mes.import')
                ->with('error', $preview['error']);
        }

        if (
            empty($preview['records']) ||
            !empty($preview['errors']) ||
            empty($preview['can_import'])
        ) {
            return redirect()
                ->route('mes.import')
                ->with(
                    'error',
                    'Import dibatalkan karena terdapat baris Excel yang tidak valid. Perbaiki file lalu upload ulang.'
                );
        }

        $result = $this->importService->executeImport(
            $preview['records'],
            $preview['dates'],
            $originalName,
            $importType
        );

        if (empty($result['success'])) {
            return redirect()
                ->route('mes.import')
                ->with(
                    'error',
                    'Import gagal: ' . ($result['error'] ?? 'Terjadi kesalahan.')
                );
        }

        $storage->delete($tempPath);

        session()->forget([
            'mes_import_temp',
            'mes_import_original_name',
            'mes_import_type',
        ]);

        return redirect()
            ->route('mes.import')
            ->with(
                'success',
                'Import berhasil. ' .
                    number_format($result['inserted_rows']) .
                    ' data baru dimasukkan, ' .
                    number_format($result['deleted_rows']) .
                    ' data lama diarsipkan pada ' .
                    number_format($result['dates_count']) .
                    ' tanggal.'
            );
    }

    public function importHistory(): View
    {
        $batches = MesImportBatch::query()
            ->active()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('actual-production.history', compact('batches'));
    }
}
