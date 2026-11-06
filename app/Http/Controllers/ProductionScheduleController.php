<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\ProductionSchedule;
use App\Models\ProductionScheduleHistory;
use App\Services\ScheduleShiftService;
use App\Models\PlannerSetting;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductionScheduleController extends Controller
{
    public function __construct(
        private ScheduleShiftService $shiftService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'machine_id' => [
                'nullable',
                Rule::exists('machines', 'id')->where(
                    fn($query) => $query->where('is_deleted', false)
                ),
            ],
            'status' => ['nullable', Rule::in(['STOP', 'ACTIVE'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = ProductionSchedule::query()
            ->select('production_schedules.*')
            ->with('machine')
            ->join('machines', 'production_schedules.machine_id', '=', 'machines.id')
            ->where('production_schedules.is_deleted', false)
            ->where('machines.is_deleted', false)
            ->whereRaw(
                "UPPER(TRIM(COALESCE(production_schedules.order_no, ''))) <> ?",
                ['STOP']
            );

        if (!empty($filters['machine_id'])) {
            $query->where(
                'production_schedules.machine_id',
                $filters['machine_id']
            );
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'STOP') {
                $query->whereRaw(
                    "UPPER(TRIM(production_schedules.order_no)) = 'STOP'"
                );
            } else {
                $query->whereRaw(
                    "UPPER(TRIM(production_schedules.order_no)) != 'STOP'"
                );
            }
        }

        if (!empty($filters['start_date'])) {
            $query->where(
                'production_schedules.end_date',
                '>=',
                $filters['start_date']
            );
        }

        if (!empty($filters['end_date'])) {
            $query->where(
                'production_schedules.start_date',
                '<=',
                $filters['end_date']
            );
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('production_schedules.order_no', 'ilike', "%{$search}%")
                    ->orWhere('production_schedules.note', 'ilike', "%{$search}%");
            });
        }

        $schedules = $query
            ->orderBy('machines.machine_code')
            ->orderBy('production_schedules.schedule_sequence')
            ->paginate(25)
            ->withQueryString();

        $machines = Machine::active()
            ->orderBy('machine_code')
            ->get();

        return view('schedules.index', compact('schedules', 'machines'));
    }

    public function create()
    {
        $machines = Machine::active()
            ->orderBy('machine_code')
            ->get();

        $shiftsPerDay = $this->getShiftsPerDay();

        return view(
            'schedules.create',
            compact('machines', 'shiftsPerDay')
        );
    }

    public function store(Request $request)
    {
        $validated = $this->validateSchedule($request);

        DB::transaction(function () use ($validated) {
            $schedule = ProductionSchedule::create($validated);

            ProductionScheduleHistory::create([
                'schedule_id' => $schedule->id,
                'change_type' => 'CREATE',
                'old_data' => null,
                'new_data' => $schedule->toArray(),
                'change_reason' => $schedule->is_special
                    ? 'Jadwal khusus hari libur ditambahkan'
                    : 'Jadwal reguler baru ditambahkan',
                'changed_at' => now(),
            ]);
        });

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Jadwal produksi berhasil ditambahkan.');
    }

    public function show(ProductionSchedule $schedule)
    {
        $schedule->load('machine');

        $history = ProductionScheduleHistory::where(
            'schedule_id',
            $schedule->id
        )
            ->orderByDesc('changed_at')
            ->get();

        return view('schedules.show', compact('schedule', 'history'));
    }

    public function edit(ProductionSchedule $schedule)
    {
        $machines = Machine::active()
            ->orderBy('machine_code')
            ->get();

        $shiftsPerDay = $this->getShiftsPerDay();

        return view(
            'schedules.edit',
            compact('schedule', 'machines', 'shiftsPerDay')
        );
    }

    public function update(Request $request, ProductionSchedule $schedule)
    {
        $validated = $this->validateSchedule($request);

        $shiftDays = 0;
        $shiftedCount = 0;

        DB::transaction(function () use (
            $validated,
            $schedule,
            &$shiftDays,
            &$shiftedCount
        ) {
            // Ambil jadwal terbaru dan kunci selama transaksi.
            $current = ProductionSchedule::active()
                ->whereKey($schedule->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldData = $current->toArray();

            $oldEndDate = Carbon::parse($current->end_date);
            $newEndDate = Carbon::parse($validated['end_date']);

            /*
         * Cascading hanya dilakukan jika:
         * 1. Mesin tidak berubah.
         * 2. Sequence tidak berubah dan tidak null.
         * 3. Jadwal sebelumnya reguler.
         * 4. Jadwal setelah diedit tetap reguler.
         */
            $sameMachine = $current->machine_id === $validated['machine_id'];

            $sameSequence = $current->schedule_sequence !== null
                && (int) $current->schedule_sequence ===
                (int) ($validated['schedule_sequence'] ?? 0);

            $canCascade = !$current->is_special
                && !$validated['is_special']
                && $sameMachine
                && $sameSequence;

            // Hitung perubahan durasi berdasarkan hari kerja.
            if ($canCascade) {
                $shiftDays = $this->shiftService->workingDayDifference(
                    $oldEndDate,
                    $newEndDate
                );
            }

            // Update jadwal yang sedang diedit.
            $current->update($validated);

            ProductionScheduleHistory::create([
                'schedule_id' => $current->id,
                'change_type' => 'UPDATE',
                'old_data' => $oldData,
                'new_data' => $current->fresh()->toArray(),
                'change_reason' => 'Jadwal produksi diperbarui',
                'changed_at' => now(),
            ]);

            // Tidak ada perubahan hari kerja, tidak perlu cascading.
            if (!$canCascade || $shiftDays === 0) {
                return;
            }

            /*
         * Ambil semua jadwal berikutnya pada mesin yang sama.
         * Hanya jadwal reguler dengan sequence lebih besar.
         */
            $followingSchedules = ProductionSchedule::active()
                ->where('machine_id', $current->machine_id)
                ->where('is_special', false)
                ->whereNotNull('schedule_sequence')
                ->where(
                    'schedule_sequence',
                    '>',
                    $current->schedule_sequence
                )
                ->orderBy('schedule_sequence')
                ->orderBy('start_date')
                ->lockForUpdate()
                ->get();

            $direction = $shiftDays > 0 ? 'mundur' : 'maju';

            foreach ($followingSchedules as $nextSchedule) {
                $nextOldData = $nextSchedule->toArray();

                // Geser tanggal mulai.
                $newStart = $this->shiftService->addWorkingDays(
                    Carbon::parse($nextSchedule->start_date),
                    $shiftDays
                );

                // Geser tanggal selesai.
                $newEnd = $this->shiftService->addWorkingDays(
                    Carbon::parse($nextSchedule->end_date),
                    $shiftDays
                );

                $nextSchedule->update([
                    'start_date' => $newStart->toDateString(),
                    'end_date' => $newEnd->toDateString(),
                ]);

                // Catat histori perubahan otomatis.
                ProductionScheduleHistory::create([
                    'schedule_id' => $nextSchedule->id,
                    'change_type' => 'SHIFT',
                    'old_data' => $nextOldData,
                    'new_data' => $nextSchedule->fresh()->toArray(),
                    'change_reason' =>
                    "Penyesuaian otomatis: jadwal sebelumnya berubah. " .
                        "Jadwal {$direction} " .
                        abs($shiftDays) .
                        " hari kerja.",
                    'changed_at' => now(),
                ]);

                $shiftedCount++;
            }
        });

        $message = 'Jadwal produksi berhasil diperbarui.';

        if ($shiftedCount > 0) {
            $direction = $shiftDays > 0 ? 'mundur' : 'maju';

            $message .= " {$shiftedCount} jadwal berikutnya ikut {$direction} " .
                abs($shiftDays) .
                ' hari kerja.';
        }

        return redirect()
            ->route('schedules.index')
            ->with('success', $message);
    }

    public function destroy(ProductionSchedule $schedule)
    {
        DB::transaction(function () use ($schedule) {
            $oldData = $schedule->fresh()->toArray();

            DB::table('production_schedules')
                ->where('id', $schedule->id)
                ->update([
                    'is_deleted' => true,
                    'updated_at' => now(),
                ]);

            ProductionScheduleHistory::create([
                'schedule_id' => $schedule->id,
                'change_type' => 'DELETE',
                'old_data' => $oldData,
                'new_data' => [
                    'is_deleted' => true,
                ],
                'change_reason' => 'Jadwal dihapus (soft delete)',
                'changed_at' => now(),
            ]);
        });

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Jadwal produksi berhasil dihapus.');
    }

    public function history(ProductionSchedule $schedule)
    {
        $schedule->load('machine');

        $history = ProductionScheduleHistory::where(
            'schedule_id',
            $schedule->id
        )
            ->orderByDesc('changed_at')
            ->paginate(20);

        return view('schedules.history', compact('schedule', 'history'));
    }

    public function shiftPreview(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => [
                'nullable',
                Rule::exists('machines', 'id')->where(
                    fn($query) => $query->where('is_deleted', false)
                ),
            ],
            'shift_days' => ['nullable', 'integer'],
        ]);

        $machines = Machine::active()
            ->orderBy('machine_code')
            ->get();

        $preview = null;
        $machineId = $validated['machine_id'] ?? null;
        $shiftDays = (int) ($validated['shift_days'] ?? 0);

        if ($machineId && $shiftDays !== 0) {
            $schedules = ProductionSchedule::active()
                ->where('machine_id', $machineId)
                ->where('is_special', false)
                ->orderBy('schedule_sequence')
                ->get([
                    'id',
                    'order_no',
                    'start_date',
                    'end_date',
                ])
                ->toArray();

            $preview = $this->shiftService->previewShift(
                $schedules,
                $shiftDays
            );
        }

        return view(
            'schedules.shift',
            compact('machines', 'preview', 'machineId', 'shiftDays')
        );
    }

    public function shiftApply(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => [
                'required',
                Rule::exists('machines', 'id')->where(
                    fn($query) => $query->where('is_deleted', false)
                ),
            ],
            'shift_days' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $machineId = $validated['machine_id'];
        $shiftDays = (int) $validated['shift_days'];
        $reason = $validated['reason']
            ?? 'Pergeseran jadwal ' . $shiftDays . ' hari kerja';

        DB::transaction(function () use ($machineId, $shiftDays, $reason) {
            $schedules = ProductionSchedule::active()
                ->where('machine_id', $machineId)
                ->where('is_special', false)
                ->orderBy('schedule_sequence')
                ->lockForUpdate()
                ->get();

            foreach ($schedules as $schedule) {
                $oldData = $schedule->toArray();

                $newStart = $this->shiftService->addWorkingDays(
                    Carbon::parse($schedule->start_date),
                    $shiftDays
                );

                $newEnd = $this->shiftService->addWorkingDays(
                    Carbon::parse($schedule->end_date),
                    $shiftDays
                );

                $schedule->update([
                    'start_date' => $newStart->toDateString(),
                    'end_date' => $newEnd->toDateString(),
                ]);

                ProductionScheduleHistory::create([
                    'schedule_id' => $schedule->id,
                    'change_type' => 'SHIFT',
                    'old_data' => $oldData,
                    'new_data' => $schedule->fresh()->toArray(),
                    'change_reason' => $reason,
                    'changed_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('schedules.index')
            ->with(
                'success',
                "Jadwal berhasil digeser {$shiftDays} hari kerja."
            );
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'machine_code',
            'schedule_sequence',
            'note',
            'order_no',
            'start_date',
            'end_date',
            'mt',
            'capacity',
            'qty_pcs',
            'mp_ideal_per_shift',
        ];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $example = [
            'CS04',
            1,
            'SC CUSTOMER',
            '4790011000-1',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+7 days')),
            2.5,
            4000,
            10000,
            5,
        ];

        foreach ($example as $index => $value) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '2', $value);
        }

        $directory = storage_path('app/temp');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = 'template_jadwal_produksi.xlsx';
        $path = $directory . '/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return response()
            ->download($path, $fileName)
            ->deleteFileAfterSend(true);
    }

    public function importForm()
    {
        return view('schedules.import');
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240',
            ],
        ]);

        $path = $request->file('excel_file')->store(
            'imports/schedules',
            'local'
        );

        if (!$path) {
            return redirect()
                ->route('schedules.import')
                ->with('error', 'File gagal disimpan.');
        }

        try {
            $spreadsheet = IOFactory::load(
                Storage::disk('local')->path($path)
            );

            $rows = $spreadsheet
                ->getActiveSheet()
                ->toArray(null, true, true, false);

            $parsed = $this->parseScheduleImport($rows);

            if ($parsed['fatal_error']) {
                Storage::disk('local')->delete($path);

                return redirect()
                    ->route('schedules.import')
                    ->with('error', $parsed['fatal_error']);
            }
        } catch (\Throwable $exception) {
            report($exception);

            Storage::disk('local')->delete($path);

            return redirect()
                ->route('schedules.import')
                ->with(
                    'error',
                    'File Excel gagal dibaca. Periksa format dan struktur kolomnya.'
                );
        }

        session([
            'schedule_import_temp' => $path,
        ]);

        $records = $parsed['records'];
        $errors = $parsed['errors'];
        $colMap = $parsed['col_map'];
        $tempPath = $path;

        return view(
            'schedules.import_preview',
            compact('records', 'errors', 'tempPath', 'colMap')
        );
    }

    public function importConfirm(Request $request)
    {
        $tempPath = session('schedule_import_temp');

        if (!$tempPath) {
            return redirect()
                ->route('schedules.import')
                ->with(
                    'error',
                    'Sesi import telah kedaluwarsa. Silakan upload ulang.'
                );
        }

        $storage = Storage::disk('local');

        if (!$storage->exists($tempPath)) {
            session()->forget('schedule_import_temp');

            return redirect()
                ->route('schedules.import')
                ->with(
                    'error',
                    'File tidak ditemukan. Silakan upload ulang.'
                );
        }

        try {
            $spreadsheet = IOFactory::load($storage->path($tempPath));

            $rows = $spreadsheet
                ->getActiveSheet()
                ->toArray(null, true, true, false);

            $parsed = $this->parseScheduleImport($rows);

            if ($parsed['fatal_error']) {
                $storage->delete($tempPath);
                session()->forget('schedule_import_temp');

                return redirect()
                    ->route('schedules.import')
                    ->with('error', $parsed['fatal_error']);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('schedules.import')
                ->with(
                    'error',
                    'File gagal diperiksa ulang. Silakan upload ulang.'
                );
        }

        $validRecords = array_values(array_filter(
            $parsed['records'],
            fn($record) => empty($record['_error'])
        ));

        $failed = count($parsed['records']) - count($validRecords);

        if (empty($validRecords)) {
            $storage->delete($tempPath);
            session()->forget('schedule_import_temp');

            return redirect()
                ->route('schedules.import')
                ->with(
                    'error',
                    'Tidak ada baris valid yang dapat diimport. Periksa kembali file Excel.'
                );
        }

        $imported = 0;

        DB::transaction(function () use (
            $validRecords,
            &$imported
        ) {
            foreach ($validRecords as $record) {
                $schedule = ProductionSchedule::create([
                    'machine_id' => $record['machine_id'],
                    'schedule_sequence' => $record['schedule_sequence'],
                    'note' => $record['note'],
                    'order_no' => $record['order_no'],
                    'start_date' => $record['start_date'],
                    'end_date' => $record['end_date'],
                    'mt' => $record['mt'],
                    'capacity' => $record['capacity'],
                    'qty_pcs' => $record['qty_pcs'],
                    'mp_ideal_per_shift' => $record['mp_ideal_per_shift'],
                    'is_special' => false,
                    'special_shifts' => null,
                ]);

                ProductionScheduleHistory::create([
                    'schedule_id' => $schedule->id,
                    'change_type' => 'CREATE',
                    'old_data' => null,
                    'new_data' => $schedule->toArray(),
                    'change_reason' => 'Jadwal diimport dari Excel',
                    'changed_at' => now(),
                ]);

                $imported++;
            }
        });

        $storage->delete($tempPath);
        session()->forget('schedule_import_temp');

        return redirect()
            ->route('schedules.index')
            ->with(
                'success',
                "Import selesai: {$imported} berhasil, {$failed} gagal."
            );
    }

    private function getShiftsPerDay(): int
    {
        return max(
            1,
            (int) PlannerSetting::getValue('shifts_per_day', 3)
        );
    }

    private function validateSchedule(Request $request): array
    {
        $shiftsPerDay = $this->getShiftsPerDay();

        $allowedShifts = range(1, $shiftsPerDay);

        $validated = $request->validate([
            'machine_id' => [
                'required',
                Rule::exists('machines', 'id')->where(
                    fn($query) => $query->where('is_deleted', false)
                ),
            ],

            'schedule_sequence' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
            'order_no' => ['required', 'string', 'max:100'],

            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],

            'mt' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'qty_pcs' => ['nullable', 'numeric', 'min:0'],
            'mp_ideal_per_shift' => ['nullable', 'numeric', 'min:0'],

            'is_special' => ['required', 'boolean'],

            'special_shifts' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    if (
                        $request->boolean('is_special')
                        && (!is_array($value) || count($value) === 0)
                    ) {
                        $fail('Pilih minimal satu shift untuk jadwal khusus.');
                    }
                },
            ],

            'special_shifts.*' => [
                'required',
                'integer',
                'distinct',
                Rule::in($allowedShifts),
            ],
        ]);

        $isSpecial = (bool) $validated['is_special'];

        if ($isSpecial) {
            /*
         * Jadwal khusus harus memiliki minimal satu tanggal
         * yang merupakan hari libur atau akhir pekan.
         */
            $start = Carbon::parse($validated['start_date'])->startOfDay();
            $end = Carbon::parse($validated['end_date'])->startOfDay();

            $containsNonWorkingDay = false;

            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                if (!$this->shiftService->isWorkingDay($cursor)) {
                    $containsNonWorkingDay = true;
                    break;
                }

                $cursor->addDay();
            }

            if (!$containsNonWorkingDay) {
                throw ValidationException::withMessages([
                    'start_date' => 'Jadwal khusus harus mencakup minimal satu tanggal libur atau akhir pekan.',
                ]);
            }

            $specialShifts = array_values(
                array_unique(
                    array_map('intval', $validated['special_shifts'])
                )
            );
        } else {
            $specialShifts = null;
        }

        $validated['is_special'] = $isSpecial;
        $validated['special_shifts'] = $specialShifts;

        return $validated;
    }

    private function parseScheduleImport(array $rows): array
    {
        $expectedColumns = [
            'machine_code',
            'schedule_sequence',
            'note',
            'order_no',
            'start_date',
            'end_date',
            'mt',
            'capacity',
            'qty_pcs',
            'mp_ideal_per_shift',
        ];

        $requiredColumns = [
            'machine_code',
            'order_no',
            'start_date',
            'end_date',
        ];

        $headerIndex = null;
        $headerRow = [];

        foreach ($rows as $index => $row) {
            $nonEmpty = array_filter(
                $row,
                fn($value) => $value !== null && $value !== ''
            );

            if (!empty($nonEmpty)) {
                $headerIndex = $index;
                $headerRow = array_map(
                    fn($value) => strtolower(trim((string) $value)),
                    $row
                );
                break;
            }
        }

        if ($headerIndex === null) {
            return [
                'records' => [],
                'errors' => [],
                'col_map' => [],
                'fatal_error' => 'File Excel tidak memiliki header atau data.',
            ];
        }

        $colMap = [];

        foreach ($headerRow as $index => $column) {
            if (in_array($column, $expectedColumns, true)) {
                $colMap[$column] = $index;
            }
        }

        $missingColumns = array_diff(
            $requiredColumns,
            array_keys($colMap)
        );

        if (!empty($missingColumns)) {
            return [
                'records' => [],
                'errors' => [],
                'col_map' => $colMap,
                'fatal_error' => 'Kolom wajib tidak ditemukan: ' .
                    implode(', ', $missingColumns),
            ];
        }

        $machineCodes = Machine::active()
            ->get(['id', 'machine_code'])
            ->mapWithKeys(fn($machine) => [
                strtoupper(trim($machine->machine_code)) => $machine->id,
            ])
            ->all();

        $records = [];
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= $headerIndex) {
                continue;
            }

            $nonEmpty = array_filter(
                $row,
                fn($value) => $value !== null && $value !== ''
            );

            if (empty($nonEmpty)) {
                continue;
            }

            $record = [];

            foreach ($expectedColumns as $field) {
                $columnIndex = $colMap[$field] ?? null;
                $record[$field] = $columnIndex !== null
                    ? ($row[$columnIndex] ?? null)
                    : null;
            }

            $record['_row'] = $rowIndex + 1;

            $rowErrors = [];

            $machineCode = strtoupper(
                trim((string) ($record['machine_code'] ?? ''))
            );

            if (!isset($machineCodes[$machineCode])) {
                $rowErrors[] = "Kode mesin '{$machineCode}' tidak ditemukan.";
            } else {
                $record['machine_id'] = $machineCodes[$machineCode];
            }

            $orderNo = trim((string) ($record['order_no'] ?? ''));

            if ($orderNo === '') {
                $rowErrors[] = 'Nomor order wajib diisi.';
            }

            $startDate = $this->normalizeExcelDate(
                $record['start_date']
            );

            $endDate = $this->normalizeExcelDate(
                $record['end_date']
            );

            if ($startDate === null) {
                $rowErrors[] = 'Tanggal mulai tidak valid.';
            }

            if ($endDate === null) {
                $rowErrors[] = 'Tanggal selesai tidak valid.';
            }

            if (
                $startDate !== null &&
                $endDate !== null &&
                $endDate < $startDate
            ) {
                $rowErrors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
            }

            $record['machine_code'] = $machineCode;
            $record['order_no'] = $orderNo;
            $record['start_date'] = $startDate;
            $record['end_date'] = $endDate;

            if (
                $record['schedule_sequence'] !== null &&
                $record['schedule_sequence'] !== ''
            ) {
                if (
                    !is_numeric($record['schedule_sequence']) ||
                    (float) $record['schedule_sequence'] < 1 ||
                    floor((float) $record['schedule_sequence']) !==
                    (float) $record['schedule_sequence']
                ) {
                    $rowErrors[] = 'Urutan jadwal harus berupa bilangan bulat minimal 1.';
                } else {
                    $record['schedule_sequence'] = (int) $record['schedule_sequence'];
                }
            } else {
                $record['schedule_sequence'] = null;
            }

            foreach (
                [
                    'mt',
                    'capacity',
                    'qty_pcs',
                    'mp_ideal_per_shift',
                ] as $field
            ) {
                $value = $record[$field];

                if ($value === null || $value === '') {
                    $record[$field] = null;
                    continue;
                }

                if (!is_numeric($value) || (float) $value < 0) {
                    $rowErrors[] = "Kolom {$field} harus berupa angka minimal 0.";
                } else {
                    $record[$field] = (float) $value;
                }
            }

            $record['note'] = $record['note'] !== null
                ? trim((string) $record['note'])
                : null;

            if (!empty($rowErrors)) {
                $record['_error'] = implode(' ', $rowErrors);

                $errors[] = 'Baris ' . $record['_row'] . ': ' .
                    $record['_error'];
            } else {
                $record['_error'] = null;
            }

            $records[] = $record;
        }

        return [
            'records' => $records,
            'errors' => $errors,
            'col_map' => $colMap,
            'fatal_error' => null,
        ];
    }

    private function normalizeExcelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::parse($value)->toDateString();
            }

            if (is_numeric($value)) {
                return Carbon::instance(
                    Date::excelToDateTimeObject((float) $value)
                )->toDateString();
            }

            return Carbon::parse(trim((string) $value))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
