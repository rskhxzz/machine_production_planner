<?php

namespace App\Http\Controllers;

use App\Models\ManpowerDailyParameter;
use App\Models\ManpowerDailyRealization;
use App\Models\ManpowerMachineOverride;
use App\Services\ManpowerCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ManpowerController extends Controller
{
    public function __construct(
        private ManpowerCalculationService $service
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::parse($validated['date'])->toDateString()
            : Carbon::today()->toDateString();

        $carbonDate = Carbon::parse($date);

        $data = $this->service->getDataForDate($carbonDate);

        $chartData = $this->service->getMonthlyChartData($carbonDate);

        $realization = ManpowerDailyRealization::active()
            ->whereDate('work_date', $date)
            ->first();

        return view('manpower.index', compact(
            'data',
            'date',
            'chartData',
            'realization'
        ));
    }

    public function updateOverride(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => [
                'required',
                Rule::exists('machines', 'id')->where(
                    fn($query) => $query->where('is_deleted', false)
                ),
            ],
            'work_date' => ['required', 'date'],
            'mp_override' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $override = DB::transaction(function () use ($validated) {
            $existing = ManpowerMachineOverride::active()
                ->where('machine_id', $validated['machine_id'])
                ->where('work_date', $validated['work_date'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'mp_override' => $validated['mp_override'],
                    'reason' => $validated['reason'] ?? $existing->reason,
                ]);

                return $existing->fresh();
            }

            return ManpowerMachineOverride::create([
                'machine_id' => $validated['machine_id'],
                'work_date' => $validated['work_date'],
                'mp_override' => $validated['mp_override'],
                'reason' => $validated['reason'] ?? null,
            ]);
        });

        return response()->json([
            'success' => true,
            'override_id' => $override->id,
            'mp_override' => (int) ceil((float) $override->mp_override),
            'message' => 'Override berhasil disimpan.',
        ]);
    }

    public function resetOverride(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => [
                'required',
                Rule::exists('machines', 'id')->where(
                    fn($query) => $query->where('is_deleted', false)
                ),
            ],
            'work_date' => ['required', 'date'],
        ]);

        ManpowerMachineOverride::active()
            ->where('machine_id', $validated['machine_id'])
            ->where('work_date', $validated['work_date'])
            ->update([
                'is_deleted' => true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Override berhasil direset ke nilai default.',
        ]);
    }

    public function updateDailyParams(Request $request)
    {
        $validated = $request->validate([
            'work_date' => ['required', 'date'],
            'tk_per_shift' => ['required', 'integer', 'min:0'],
            'magang_per_shift' => ['required', 'integer', 'min:0'],
            'non_shift_assistance_per_day' => ['required', 'integer', 'min:0'],
            'additional_manpower_per_day' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $params = ManpowerDailyParameter::active()
                ->where('work_date', $validated['work_date'])
                ->lockForUpdate()
                ->first();

            $values = [
                'tk_per_shift' => $validated['tk_per_shift'],
                'magang_per_shift' => $validated['magang_per_shift'],
                'non_shift_assistance_per_day' => $validated['non_shift_assistance_per_day'],
                'additional_manpower_per_day' => $validated['additional_manpower_per_day'],
            ];

            if ($params) {
                $params->forceFill($values);
                $params->save();
            } else {
                $params = new ManpowerDailyParameter();

                $params->forceFill([
                    'work_date' => $validated['work_date'],
                    ...$values,
                ]);

                $params->save();
            }
        });

        $data = $this->service->getDataForDate(
            Carbon::parse($validated['work_date'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Parameter harian berhasil disimpan.',
            'summary' => [
                'total_mp_shift' => $data['total_mp_shift'],
                'total_mp_day' => $data['total_mp_day'],
                'os_per_shift' => $data['os_per_shift'],
                'os_per_day' => $data['os_per_day'],
            ],
        ]);
    }

    public function updateRealization(Request $request)
    {
        $validated = $request->validate([
            'work_date' => ['required', 'date'],
            'os_per_day' => ['required', 'integer', 'min:0'],
            'tk_per_day' => ['required', 'integer', 'min:0'],
            'magang_per_day' => ['required', 'integer', 'min:0'],
            'non_shift_assistance_per_day' => ['required', 'integer', 'min:0'],
            'additional_manpower_per_day' => ['required', 'integer', 'min:0'],
        ]);

        $realization = DB::transaction(function () use ($validated) {
            $existing = ManpowerDailyRealization::active()
                ->where('work_date', $validated['work_date'])
                ->lockForUpdate()
                ->first();

            $values = [
                'os_per_day' => $validated['os_per_day'],
                'tk_per_day' => $validated['tk_per_day'],
                'magang_per_day' => $validated['magang_per_day'],
                'non_shift_assistance_per_day' => $validated['non_shift_assistance_per_day'],
                'additional_manpower_per_day' => $validated['additional_manpower_per_day'],
            ];

            if ($existing) {
                $existing->forceFill($values);
                $existing->save();

                return $existing->fresh();
            }

            $realization = new ManpowerDailyRealization();

            $realization->forceFill([
                'work_date' => $validated['work_date'],
                ...$values,
            ]);

            $realization->save();

            return $realization;
        });

        $data = $this->service->getDataForDate(
            Carbon::parse($validated['work_date'])
        );

        $completedTotal = $realization->total_realization;
        $plannedTotal = $data['planned_total'];

        return response()->json([
            'success' => true,
            'message' => 'Realisasi manpower berhasil disimpan.',
            'summary' => [
                'planned_total' => $plannedTotal,
                'completed_total' => $completedTotal,
                'gap' => max(0, $plannedTotal - $completedTotal),
                'surplus' => max(0, $completedTotal - $plannedTotal),
                'os_per_day' => $realization->os_per_day,
                'os_per_shift' => (int) ceil(
                    $realization->os_per_day / max(1, $data['os_shifts_per_day'])
                ),
            ],
        ]);
    }
}
