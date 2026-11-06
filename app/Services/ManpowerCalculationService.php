<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\ManpowerDailyParameter;
use App\Models\ManpowerDailyRealization;
use App\Models\ManpowerMachineOverride;
use App\Models\ProductionSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ManpowerCalculationService
{
    private const MACHINE_SHIFTS_PER_DAY = 3;
    private const OS_SHIFTS_PER_DAY = 2;
    private const WEEKEND_DAYS = [6, 7];

    public function __construct(
        private ScheduleShiftService $shiftService
    ) {}

    public function getDataForDate(Carbon $date): array
    {
        $dateStr = $date->toDateString();

        $isWorkingDay = $this->shiftService->isWorkingDay($date);
        $isWeekend = in_array(
            $date->isoWeekday(),
            self::WEEKEND_DAYS,
            true
        );

        $shiftsPerDay = self::MACHINE_SHIFTS_PER_DAY;
        $osShiftsPerDay = self::OS_SHIFTS_PER_DAY;
        $magangShiftsPerDay = $isWeekend ? 0 : 2;
        $tkShiftsPerDay = $isWeekend ? 0 : 3;

        $params = ManpowerDailyParameter::active()
            ->whereDate('work_date', $dateStr)
            ->first();

        $tkPerShift = $isWeekend
            ? 0
            : max(0, (int) ($params?->tk_per_shift ?? 9));

        $magangPerShift = $isWeekend
            ? 0
            : max(0, (int) ($params?->magang_per_shift ?? 3));

        $nonShiftAssistancePerDay = max(
            0,
            (int) ($params?->non_shift_assistance_per_day ?? 0)
        );

        $additionalManpowerPerDay = max(
            0,
            (int) ($params?->additional_manpower_per_day ?? 0)
        );

        $machines = Machine::active()
            ->orderBy('machine_type')
            ->orderBy('machine_code')
            ->get();

        $machineIds = $machines->pluck('id');

        $schedulesByMachine = ProductionSchedule::active()
            ->whereIn('machine_id', $machineIds)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->orderBy('schedule_sequence')
            ->get()
            ->groupBy('machine_id');

        $overrides = ManpowerMachineOverride::active()
            ->whereDate('work_date', $dateStr)
            ->whereIn('machine_id', $machineIds)
            ->get()
            ->keyBy('machine_id');

        $machineData = [];
        $orderDetails = [];

        $totalMpByMachineShift = array_fill(
            1,
            $shiftsPerDay,
            0
        );

        foreach ($machines as $machine) {
            $activeSchedules = $schedulesByMachine->get(
                $machine->id,
                collect()
            );

            $override = $overrides->get($machine->id);

            $overrideMp = $override !== null
                ? (int) ceil((float) $override->mp_override)
                : null;

            $machineMpByShift = [];
            $machineOrders = [];
            $machineDefaultMpValues = [];

            $machineHasSchedule = false;
            $machineHasProductionOrder = false;
            $machineHasStop = false;

            for ($shift = 1; $shift <= $shiftsPerDay; $shift++) {
                $shiftSchedules = $activeSchedules
                    ->filter(function (
                        ProductionSchedule $schedule
                    ) use ($isWorkingDay, $shift) {
                        if ($schedule->is_special) {
                            if ($isWorkingDay) {
                                return false;
                            }

                            $selectedShifts = array_map(
                                'intval',
                                $schedule->special_shifts ?? []
                            );

                            return in_array(
                                $shift,
                                $selectedShifts,
                                true
                            );
                        }

                        return $isWorkingDay;
                    })
                    ->values();

                $hasSchedule = $shiftSchedules->isNotEmpty();

                $productionSchedules = $shiftSchedules
                    ->filter(function (
                        ProductionSchedule $schedule
                    ) {
                        return !$this->isStopSchedule($schedule)
                            && !empty(trim((string) $schedule->order_no));
                    })
                    ->values();

                $hasProductionOrders = $productionSchedules->isNotEmpty();

                $stopSchedules = $shiftSchedules
                    ->filter(
                        fn(ProductionSchedule $schedule) =>
                        $this->isStopSchedule($schedule)
                    )
                    ->values();

                $isStop = !$hasProductionOrders
                    && $stopSchedules->isNotEmpty();

                if ($hasProductionOrders) {
                    $effectiveSchedules = $productionSchedules;
                } elseif ($isStop) {
                    $effectiveSchedules = $stopSchedules;
                } else {
                    $effectiveSchedules = $shiftSchedules;
                }

                $defaultMp = null;

                if ($hasProductionOrders) {
                    $maxMp = $productionSchedules
                        ->whereNotNull('mp_ideal_per_shift')
                        ->max('mp_ideal_per_shift');

                    $defaultMp = $maxMp !== null
                        ? (int) ceil((float) $maxMp)
                        : 0;
                } elseif ($isStop) {
                    $defaultMp = 0;
                } elseif ($hasSchedule) {
                    $maxMp = $effectiveSchedules
                        ->whereNotNull('mp_ideal_per_shift')
                        ->max('mp_ideal_per_shift');

                    $defaultMp = $maxMp !== null
                        ? (int) ceil((float) $maxMp)
                        : 0;
                }

                $allowOverride = $isWorkingDay || $hasSchedule;

                $mpUsed = $allowOverride
                    ? ($overrideMp ?? $defaultMp ?? 0)
                    : 0;

                $machineMpByShift[$shift] = $mpUsed;

                $totalMpByMachineShift[$shift] += $mpUsed;

                $machineHasSchedule =
                    $machineHasSchedule || $hasSchedule;

                $machineHasProductionOrder =
                    $machineHasProductionOrder || $hasProductionOrders;

                $machineHasStop =
                    $machineHasStop || $isStop;

                if ($defaultMp !== null) {
                    $machineDefaultMpValues[] = $defaultMp;
                }

                foreach ($effectiveSchedules as $schedule) {
                    $scheduleId = $schedule->id;

                    if (!isset($machineOrders[$scheduleId])) {
                        $isOrderStop = $this->isStopSchedule($schedule);

                        $machineOrders[$scheduleId] = [
                            'schedule_id' => $schedule->id,
                            'machine_id' => $machine->id,
                            'machine_code' => $machine->machine_code,
                            'order_no' => $schedule->order_no,
                            'note' => $schedule->note,
                            'label' => $isOrderStop
                                ? 'STOP'
                                : ($schedule->note ?: $schedule->order_no),
                            'start_date' => $schedule->start_date
                                ? Carbon::parse(
                                    $schedule->start_date
                                )->toDateString()
                                : null,
                            'end_date' => $schedule->end_date
                                ? Carbon::parse(
                                    $schedule->end_date
                                )->toDateString()
                                : null,
                            'mp_ideal_per_shift' => $schedule->mp_ideal_per_shift !== null
                                ? (int) ceil(
                                    (float) $schedule->mp_ideal_per_shift
                                )
                                : null,
                            'is_stop' => $isOrderStop,
                            'is_special' => (bool) $schedule->is_special,
                            'special_shifts' => $schedule->special_shifts ?? [],
                            'shifts' => [],
                        ];
                    }

                    $machineOrders[$scheduleId]['shifts'][] = $shift;
                }
            }

            $machineOrders = array_values(
                array_map(function ($order) {
                    $order['shifts'] = array_values(
                        array_unique($order['shifts'])
                    );

                    return $order;
                }, $machineOrders)
            );

            $defaultMp = !empty($machineDefaultMpValues)
                ? max($machineDefaultMpValues)
                : null;

            $machineMpUsed = !empty($machineMpByShift)
                ? max($machineMpByShift)
                : 0;

            $machineData[] = [
                'machine_id' => $machine->id,
                'machine_code' => $machine->machine_code,
                'machine_name' => $machine->machine_name,
                'machine_type' => $machine->machine_type,
                'is_stop' => $machineHasStop
                    && !$machineHasProductionOrder,
                'has_schedule' => $machineHasSchedule,
                'has_production_order' => $machineHasProductionOrder,
                'default_mp' => $defaultMp,
                'override_mp' => $overrideMp,
                'override_id' => $override?->id,
                'override_reason' => $override?->reason,
                'mp_used' => $machineMpUsed,
                'mp_by_shift' => $machineMpByShift,
                'orders' => $machineOrders,
            ];

            foreach ($machineOrders as $order) {
                $orderDetails[] = $order;
            }
        }

        $tkByMachineShift = [];
        $magangByMachineShift = [];
        $residualOsByMachineShift = [];

        for ($shift = 1; $shift <= $shiftsPerDay; $shift++) {
            $machineMp = (int) (
                $totalMpByMachineShift[$shift] ?? 0
            );

            $tkByMachineShift[$shift] = $isWeekend
                ? 0
                : $tkPerShift;

            $magangByMachineShift[$shift] = (
                !$isWeekend && $shift <= 2
            )
                ? $magangPerShift
                : 0;

            $residualOsByMachineShift[$shift] = max(
                0,
                $machineMp
                    - $tkByMachineShift[$shift]
                    - $magangByMachineShift[$shift]
            );
        }

        $osByShift = [
            1 => max(
                $residualOsByMachineShift[1],
                $residualOsByMachineShift[2]
            ),
            2 => max(
                $residualOsByMachineShift[2],
                $residualOsByMachineShift[3]
            ),
        ];

        $remainingDeduction = (
            $nonShiftAssistancePerDay
            + $additionalManpowerPerDay
        );

        $osPriority = [1, 2];

        usort(
            $osPriority,
            fn($a, $b) => $osByShift[$b] <=> $osByShift[$a]
        );

        foreach ($osPriority as $shift) {
            if ($remainingDeduction <= 0) {
                break;
            }

            $deduction = min(
                $osByShift[$shift],
                $remainingDeduction
            );

            $osByShift[$shift] -= $deduction;
            $remainingDeduction -= $deduction;
        }

        $osShift1 = $osByShift[1];
        $osShift2 = $osByShift[2];

        $osPerDay = $osShift1 + $osShift2;
        $osPerShift = max($osShift1, $osShift2);

        $tkPerDay = array_sum($tkByMachineShift);
        $magangPerDay = array_sum($magangByMachineShift);

        $machineMpPerDay = array_sum($totalMpByMachineShift);

        $plannedTotal = (
            $osPerDay
            + $tkPerDay
            + $magangPerDay
            + $nonShiftAssistancePerDay
            + $additionalManpowerPerDay
        );

        $averageMpPerShift = $shiftsPerDay > 0
            ? round($machineMpPerDay / $shiftsPerDay, 2)
            : 0;

        $dailyParams = [
            'tk_per_shift' => $tkPerShift,
            'magang_per_shift' => $magangPerShift,
            'non_shift_assistance_per_day' => $nonShiftAssistancePerDay,
            'additional_manpower_per_day' => $additionalManpowerPerDay,
        ];

        $plannedManpower = [
            'os_shift_1' => $osShift1,
            'os_shift_2' => $osShift2,
            'os_per_shift' => $osPerShift,
            'os_per_day' => $osPerDay,
            'tk_per_shift' => $tkPerShift,
            'tk_per_day' => $tkPerDay,
            'magang_per_shift' => $magangPerShift,
            'magang_per_day' => $magangPerDay,
            'non_shift_assistance_per_day' => $nonShiftAssistancePerDay,
            'additional_manpower_per_day' => $additionalManpowerPerDay,
            'total_planned' => $plannedTotal,
        ];

        $shiftBreakdown = [];

        for ($shift = 1; $shift <= $shiftsPerDay; $shift++) {
            $shiftBreakdown[$shift] = [
                'shift' => $shift,
                'total_mp' => $totalMpByMachineShift[$shift],
                'tk' => $tkByMachineShift[$shift],
                'magang' => $magangByMachineShift[$shift],
                'os' => $residualOsByMachineShift[$shift],
            ];
        }

        $osShiftBreakdown = [
            1 => [
                'shift' => 1,
                'duration_hours' => 12,
                'os' => $osShift1,
            ],
            2 => [
                'shift' => 2,
                'duration_hours' => 12,
                'os' => $osShift2,
            ],
        ];

        return [
            'date' => $dateStr,
            'is_working_day' => $isWorkingDay,
            'is_weekend' => $isWeekend,
            'machines' => $machineData,
            'orders' => $orderDetails,
            'daily_params' => $dailyParams,
            'planned_manpower' => $plannedManpower,
            'has_custom_params' => $params !== null,
            'params_id' => $params?->id,
            'shifts_per_day' => $shiftsPerDay,
            'os_shifts_per_day' => $osShiftsPerDay,
            'magang_shifts_per_day' => $magangShiftsPerDay,
            'tk_shifts_per_day' => $tkShiftsPerDay,
            'machine_mp_day' => $machineMpPerDay,
            'total_mp_shift' => max($totalMpByMachineShift),
            'total_mp_day' => $plannedTotal,
            'total_mp_by_shift' => $totalMpByMachineShift,
            'shift_breakdown' => $shiftBreakdown,
            'os_shift_breakdown' => $osShiftBreakdown,
            'os_by_shift' => $osByShift,
            'tk_by_shift' => $tkByMachineShift,
            'magang_by_shift' => $magangByMachineShift,
            'average_mp_per_shift' => $averageMpPerShift,
            'tk_per_shift' => $tkPerShift,
            'tk_per_day' => $tkPerDay,
            'magang_per_shift' => $magangPerShift,
            'magang_per_day' => $magangPerDay,
            'non_shift_assistance_per_day' => $nonShiftAssistancePerDay,
            'additional_manpower_per_day' => $additionalManpowerPerDay,
            'os_per_shift' => $osPerShift,
            'os_per_day' => $osPerDay,
            'planned_total' => $plannedTotal,
            'required_os_per_shift' => $osPerShift,
            'required_os_per_day' => $osPerDay,
            'long_shift_os' => $osPerShift,
            'two_shift_os' => $osPerDay,
        ];
    }

    public function getMonthlyChartData(Carbon $date): array
    {
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->endOfMonth();

        $realizations = ManpowerDailyRealization::active()
            ->whereBetween('work_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get()
            ->keyBy(
                fn($item) => $item->work_date->toDateString()
            );

        $days = [];
        $maxTotal = 1;

        foreach (CarbonPeriod::create($startDate, $endDate) as $day) {
            $dateStr = $day->toDateString();

            $data = $this->getDataForDate($day);

            $plan = [
                'os' => $data['os_per_day'],
                'tk' => $data['tk_per_day'],
                'magang' => $data['magang_per_day'],
                'non_shift_assistance' => $data['non_shift_assistance_per_day'],
                'additional_manpower' => $data['additional_manpower_per_day'],
            ];

            $planTotal = array_sum($plan);

            $realization = $realizations->get($dateStr);

            $actual = null;
            $actualTotal = null;

            if ($realization) {
                $actual = [
                    'os' => (int) $realization->os_per_day,
                    'tk' => (int) $realization->tk_per_day,
                    'magang' => (int) $realization->magang_per_day,
                    'non_shift_assistance' => (int) $realization->non_shift_assistance_per_day,
                    'additional_manpower' => (int) $realization->additional_manpower_per_day,
                ];

                $actualTotal = array_sum($actual);
            }

            $maxTotal = max(
                $maxTotal,
                $planTotal,
                $actualTotal ?? 0
            );

            $days[] = [
                'date' => $dateStr,
                'day' => $day->format('d'),
                'is_today' => $dateStr === Carbon::today()->toDateString(),
                'is_working_day' => $data['is_working_day'],
                'plan' => $plan,
                'plan_total' => $planTotal,
                'actual' => $actual,
                'actual_total' => $actualTotal,
                'has_realization' => $realization !== null,
                'gap' => $actualTotal !== null
                    ? max(0, $planTotal - $actualTotal)
                    : null,
                'surplus' => $actualTotal !== null
                    ? max(0, $actualTotal - $planTotal)
                    : null,
            ];
        }

        return [
            'month_label' => $startDate->isoFormat('MMMM YYYY'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'days' => $days,
            'max_total' => $maxTotal,
        ];
    }

    private function isStopSchedule(ProductionSchedule $schedule): bool
    {
        if (method_exists($schedule, 'isStopOrder')) {
            return (bool) $schedule->isStopOrder();
        }

        $text = strtoupper(
            trim(
                ($schedule->order_no ?? '')
                    . ' '
                    . ($schedule->note ?? '')
            )
        );

        return preg_match('/\bSTOP\b/', $text) === 1;
    }
}
