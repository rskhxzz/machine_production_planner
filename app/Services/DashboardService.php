<?php

namespace App\Services;

use App\Models\MesProduction;
use App\Models\ProductionSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private ManpowerCalculationService $manpowerCalculationService
    ) {}

    public function getSummary(string $startDate, string $endDate): array
    {
        $machineCodesAuto = ['CS04', 'CS05', 'CS06', 'CS07', 'CS08', 'CS09'];
        $machineCodesSemi = ['CS01', 'CS02', 'CS03', 'CS11', 'CS12', 'CS13', 'CS14', 'CS15', 'CS16'];

        $totalScheduled = ProductionSchedule::active()
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->whereRaw("UPPER(TRIM(order_no)) != 'STOP'")
            ->count();

        $activeMachinesCount = ProductionSchedule::active()
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->whereRaw("UPPER(TRIM(order_no)) != 'STOP'")
            ->distinct()
            ->count('machine_id');

        $latestMesDate = MesProduction::active()->max('datprd');

        $autoActual = DB::table('mes_productions')
            ->where('is_deleted', false)
            ->whereIn('mcnnam', $machineCodesAuto)
            ->whereBetween('datprd', [$startDate, $endDate])
            ->selectRaw('SUM(COALESCE(prd_a, 0) + COALESCE(prd_b, 0)) / 1000.0 AS total_ton')
            ->value('total_ton');

        $semiActual = DB::table('mes_productions')
            ->where('is_deleted', false)
            ->whereIn('mcnnam', $machineCodesSemi)
            ->whereBetween('datprd', [$startDate, $endDate])
            ->selectRaw('SUM(COALESCE(qtyprd, 0)) AS total_pcs')
            ->value('total_pcs');

        $today = Carbon::today();
        $todayStr = $today->toDateString();

        $stopMachines = DB::table('production_schedules as ps')
            ->join('machines as m', 'ps.machine_id', '=', 'm.id')
            ->where('ps.is_deleted', false)
            ->where('m.is_deleted', false)
            ->where('ps.start_date', '<=', $todayStr)
            ->where('ps.end_date', '>=', $todayStr)
            ->whereRaw("UPPER(TRIM(ps.order_no)) = 'STOP'")
            ->select('m.machine_code', 'ps.start_date', 'ps.end_date')
            ->orderBy('m.machine_code')
            ->get();

        $runningSchedules = DB::table('production_schedules as ps')
            ->join('machines as m', 'ps.machine_id', '=', 'm.id')
            ->where('ps.is_deleted', false)
            ->where('m.is_deleted', false)
            ->where('ps.start_date', '<=', $todayStr)
            ->where('ps.end_date', '>=', $todayStr)
            ->whereRaw("UPPER(TRIM(ps.order_no)) != 'STOP'")
            ->select(
                'm.machine_code',
                'ps.order_no',
                'ps.note',
                'ps.start_date',
                'ps.end_date',
                'ps.mp_ideal_per_shift'
            )
            ->orderBy('m.machine_code')
            ->get();

        $nextSchedules = DB::table('production_schedules as ps')
            ->join('machines as m', 'ps.machine_id', '=', 'm.id')
            ->where('ps.is_deleted', false)
            ->where('m.is_deleted', false)
            ->where('ps.start_date', '>', $todayStr)
            ->whereRaw("UPPER(TRIM(ps.order_no)) != 'STOP'")
            ->select(
                'm.machine_code',
                'ps.order_no',
                'ps.note',
                'ps.start_date',
                'ps.end_date'
            )
            ->orderBy('m.machine_code')
            ->orderBy('ps.start_date')
            ->limit(10)
            ->get();

        $trend = DB::table('mes_productions')
            ->where('is_deleted', false)
            ->whereBetween('datprd', [$startDate, $endDate])
            ->selectRaw("
                datprd::text AS date_str,
                SUM(
                    CASE
                        WHEN mcnnam IN ('CS04', 'CS05', 'CS06', 'CS07', 'CS08', 'CS09')
                        THEN COALESCE(prd_a, 0) + COALESCE(prd_b, 0)
                        ELSE 0
                    END
                ) / 1000.0 AS auto_ton,
                SUM(
                    CASE
                        WHEN mcnnam IN ('CS01', 'CS02', 'CS03', 'CS11', 'CS12', 'CS13', 'CS14', 'CS15', 'CS16')
                        THEN COALESCE(qtyprd, 0)
                        ELSE 0
                    END
                ) AS semi_pcs
            ")
            ->groupBy('datprd')
            ->orderBy('datprd')
            ->get();

        $manpower = $this->manpowerCalculationService->getDataForDate($today);

        return [
            'total_scheduled' => $totalScheduled,
            'active_machines_count' => $activeMachinesCount,
            'auto_actual_ton' => round((float) $autoActual, 3),
            'semi_actual_pcs' => round((float) $semiActual, 0),
            'total_mp_shift' => $manpower['total_mp_shift'],
            'total_mp_day' => $manpower['total_mp_day'],
            'two_shift_os' => $manpower['two_shift_os'],
            'latest_mes_date' => $latestMesDate,
            'stop_machines' => $stopMachines,
            'running_schedules' => $runningSchedules,
            'next_schedules' => $nextSchedules,
            'trend' => $trend,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
