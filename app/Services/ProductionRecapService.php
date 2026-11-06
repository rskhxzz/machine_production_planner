<?php

namespace App\Services;

use App\Models\Machine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductionRecapService
{
    private const MACHINE_CODES_AUTO = [
        'CS04',
        'CS05',
        'CS06',
        'CS07',
        'CS08',
        'CS09',
    ];

    private const MACHINE_CODES_SEMI = [
        'CS01',
        'CS02',
        'CS03',
        'CS11',
        'CS12',
        'CS13',
        'CS14',
        'CS15',
        'CS16',
    ];

    public function getRecap(array $filters = []): array
    {
        $date = Carbon::parse(
            $filters['date'] ?? Carbon::today()->toDateString()
        )->toDateString();

        $machineCodes = array_merge(
            self::MACHINE_CODES_AUTO,
            self::MACHINE_CODES_SEMI
        );

        $machineType = strtoupper(
            trim((string) ($filters['machine_type'] ?? ''))
        );

        $machineId = $filters['machine_id'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        if ($machineType === 'AUTO') {
            $machineCodes = self::MACHINE_CODES_AUTO;
        } elseif ($machineType === 'SEMI') {
            $machineCodes = self::MACHINE_CODES_SEMI;
        }

        if ($machineId) {
            $machine = Machine::active()->find($machineId);

            if ($machine) {
                $selectedCode = strtoupper(
                    trim($machine->machine_code)
                );

                $machineCodes = array_values(
                    array_filter(
                        $machineCodes,
                        fn($code) => strtoupper($code) === $selectedCode
                    )
                );
            } else {
                $machineCodes = [];
            }
        }

        $rows = [];
        $rowsByKey = [];
        $demandMap = [];
        $scheduleStartMap = [];
        $scheduleMetaMap = [];
        $cumulativeMap = [];

        if (!empty($machineCodes)) {
            $scheduleBase = DB::table('production_schedules as ps')
                ->join(
                    'machines as m',
                    'ps.machine_id',
                    '=',
                    'm.id'
                )
                ->where('ps.is_deleted', false)
                ->where('m.is_deleted', false)
                ->whereIn('m.machine_code', $machineCodes)
                ->whereNotNull('ps.order_no')
                ->whereNotNull('ps.start_date')
                ->whereRaw(
                    "TRIM(COALESCE(ps.order_no, '')) <> ''"
                )
                ->whereRaw(
                    "UPPER(TRIM(COALESCE(ps.order_no, ''))) <> ?",
                    ['STOP']
                );

            $latestScheduleRows = (clone $scheduleBase)
                ->whereDate('ps.start_date', '<=', $date)
                ->select([
                    'm.machine_code',
                    'ps.order_no',
                    'ps.qty_pcs',
                    'ps.mt',
                    'ps.capacity',
                    'ps.start_date',
                    'ps.updated_at',
                ])
                ->orderByDesc('ps.start_date')
                ->orderByDesc('ps.updated_at')
                ->get();

            foreach ($latestScheduleRows as $item) {
                $key = $this->makeKey(
                    $item->machine_code,
                    $item->order_no
                );

                $startDate = Carbon::parse(
                    $item->start_date
                )->toDateString();

                $updatedAt = $item->updated_at
                    ? Carbon::parse($item->updated_at)->getTimestamp()
                    : 0;

                $existingStartDate = $scheduleMetaMap[$key]['start_date'] ?? null;
                $existingUpdatedAt = $scheduleMetaMap[$key]['updated_at'] ?? -1;

                $isNewerSchedule = !isset($scheduleMetaMap[$key])
                    || $startDate > $existingStartDate
                    || (
                        $startDate === $existingStartDate
                        && $updatedAt > $existingUpdatedAt
                    );

                if (!$isNewerSchedule) {
                    continue;
                }

                $scheduleMetaMap[$key] = [
                    'start_date' => $startDate,
                    'updated_at' => $updatedAt,
                ];

                $scheduleStartMap[$key] = $startDate;

                $demandMap[$key] = [
                    'demand_pcs' => (float) ($item->qty_pcs ?? 0),
                    'demand_ton' => (float) ($item->mt ?? 0),
                    'capacity_per_shift' => (float) ($item->capacity ?? 0),
                ];
            }

            $activeSchedules = (clone $scheduleBase)
                ->whereDate('ps.start_date', '<=', $date)
                ->whereDate('ps.end_date', '>=', $date)
                ->select([
                    'm.machine_code',
                    'ps.order_no',
                ])
                ->distinct()
                ->get();

            foreach ($activeSchedules as $item) {
                $key = $this->makeKey(
                    $item->machine_code,
                    $item->order_no
                );

                $rowsByKey[$key] = $this->emptyRow(
                    $item->machine_code,
                    $item->order_no
                );
            }

            $dailyRows = DB::table('mes_productions')
                ->where('is_deleted', false)
                ->whereDate('datprd', $date)
                ->whereIn('mcnnam', $machineCodes)
                ->whereNotNull('prpno')
                ->whereRaw("TRIM(prpno) <> ''")
                ->selectRaw("
                    mcnnam,
                    prpno,
                    MAX(custnam) AS customer,
                    SUM(COALESCE(qtyprd, 0)) AS daily_output_pcs,
                    SUM(
                        COALESCE(wst_prd, 0)
                        + COALESCE(sisiran, 0)
                        + COALESCE(sobekan, 0)
                        + COALESCE(score, 0)
                        + COALESCE(wgtgf, 0)
                    ) AS waste_kg,
                    SUM(COALESCE(bp, 0)) AS bp_kg
                ")
                ->groupBy('mcnnam', 'prpno')
                ->get();

            foreach ($dailyRows as $item) {
                $key = $this->makeKey(
                    $item->mcnnam,
                    $item->prpno
                );

                if (!isset($rowsByKey[$key])) {
                    $rowsByKey[$key] = $this->emptyRow(
                        $item->mcnnam,
                        $item->prpno
                    );
                }

                $rowsByKey[$key]['customer'] = $item->customer;
                $rowsByKey[$key]['daily_output_pcs'] =
                    (float) $item->daily_output_pcs;

                $rowsByKey[$key]['waste_kg'] =
                    (float) $item->waste_kg;

                $rowsByKey[$key]['bp_kg'] =
                    (float) $item->bp_kg;
            }

            $minimumStartDate = !empty($scheduleStartMap)
                ? min($scheduleStartMap)
                : $date;

            $cumulativeRows = DB::table('mes_productions')
                ->where('is_deleted', false)
                ->whereDate('datprd', '>=', $minimumStartDate)
                ->whereDate('datprd', '<=', $date)
                ->whereIn('mcnnam', $machineCodes)
                ->whereNotNull('prpno')
                ->whereRaw("TRIM(prpno) <> ''")
                ->selectRaw("
                    mcnnam,
                    prpno,
                    datprd,
                    MAX(custnam) AS customer,
                    SUM(COALESCE(qtyprd, 0)) AS output_pcs
                ")
                ->groupBy(
                    'mcnnam',
                    'prpno',
                    'datprd'
                )
                ->get();

            foreach ($cumulativeRows as $item) {
                $key = $this->makeKey(
                    $item->mcnnam,
                    $item->prpno
                );

                $cycleStartDate = $scheduleStartMap[$key] ?? $date;

                $productionDate = Carbon::parse(
                    $item->datprd
                )->toDateString();

                if ($productionDate < $cycleStartDate) {
                    continue;
                }

                if (!isset($cumulativeMap[$key])) {
                    $cumulativeMap[$key] = [
                        'customer' => $item->customer,
                        'cumulative_output_pcs' => 0,
                    ];
                }

                $cumulativeMap[$key]['cumulative_output_pcs'] +=
                    (float) $item->output_pcs;
            }

            foreach ($rowsByKey as $key => $row) {
                $demand = $demandMap[$key] ?? [
                    'demand_pcs' => 0,
                    'demand_ton' => 0,
                    'capacity_per_shift' => 0,
                ];

                $cumulative = $cumulativeMap[$key] ?? [
                    'customer' => null,
                    'cumulative_output_pcs' => 0,
                ];

                $demandPcs = $demand['demand_pcs'];
                $demandTon = $demand['demand_ton'] ?? 0;

                $dailyOutput = $row['daily_output_pcs'];
                $cumulativeOutput = $cumulative['cumulative_output_pcs'];

                $tonPerPcs = $demandPcs > 0
                    ? $demandTon / $demandPcs
                    : 0;

                $wasteKg = $row['waste_kg'];
                $bpKg = $row['bp_kg'];

                $row['customer'] = $row['customer']
                    ?: $cumulative['customer'];

                $row['demand_pcs'] = $demandPcs;
                $row['demand_ton'] = $demandTon;

                $row['daily_output_pcs'] = $dailyOutput;
                $row['output_ton'] = $dailyOutput * $tonPerPcs;

                $row['cumulative_output_pcs'] = $cumulativeOutput;
                $row['cumulative_ton'] = $cumulativeOutput * $tonPerPcs;

                $row['remaining_pcs'] = $demandPcs - $cumulativeOutput;
                $row['remaining_ton'] = $demandTon - $row['cumulative_ton'];

                $row['capacity_per_shift'] =
                    $demand['capacity_per_shift'];

                $row['cumulative_output_pcs'] =
                    $cumulativeOutput;

                $row['remaining_pcs'] =
                    $demandPcs - $cumulativeOutput;

                $row['waste_kg'] = $wasteKg;

                $row['waste_pct'] = $bpKg > 0
                    ? ($wasteKg / $bpKg) * 100
                    : 0;

                $rows[] = $row;
            }
        }

        if ($search !== '') {
            $rows = array_values(
                array_filter(
                    $rows,
                    function ($row) use ($search) {
                        return stripos(
                            $row['order_no'],
                            $search
                        ) !== false
                            || stripos(
                                (string) ($row['customer'] ?? ''),
                                $search
                            ) !== false
                            || stripos(
                                $row['machine_code'],
                                $search
                            ) !== false;
                    }
                )
            );
        }

        usort($rows, function ($a, $b) {
            return strcmp(
                $a['machine_code'] . $a['order_no'],
                $b['machine_code'] . $b['order_no']
            );
        });

        $autoRows = array_values(
            array_filter(
                $rows,
                fn($row) => $row['machine_type'] === 'AUTO'
            )
        );

        $semiRows = array_values(
            array_filter(
                $rows,
                fn($row) => $row['machine_type'] === 'SEMI'
            )
        );

        return [
            'date' => $date,
            'rows' => $rows,
            'auto_rows' => $autoRows,
            'semi_rows' => $semiRows,
            'auto_summary' => $this->buildSummary($autoRows),
            'semi_summary' => $this->buildSummary($semiRows),
            'machine_codes_auto' => self::MACHINE_CODES_AUTO,
            'machine_codes_semi' => self::MACHINE_CODES_SEMI,
        ];
    }

    private function emptyRow(
        string $machineCode,
        string $orderNo
    ): array {
        $machineCode = strtoupper(trim($machineCode));

        return [
            'machine_code' => $machineCode,
            'machine_type' => in_array(
                $machineCode,
                self::MACHINE_CODES_AUTO,
                true
            ) ? 'AUTO' : 'SEMI',
            'order_no' => trim($orderNo),
            'customer' => null,
            'demand_pcs' => 0,
            'capacity_per_shift' => 0,
            'daily_output_pcs' => 0,
            'cumulative_output_pcs' => 0,
            'remaining_pcs' => 0,
            'demand_ton' => 0,
            'output_ton' => 0,
            'cumulative_ton' => 0,
            'remaining_ton' => 0,
            'waste_kg' => 0,
            'bp_kg' => 0,
            'waste_pct' => 0,
        ];
    }

    private function makeKey(
        string $machineCode,
        string $orderNo
    ): string {
        return strtoupper(trim($machineCode))
            . '|'
            . strtoupper(trim($orderNo));
    }

    private function buildSummary(array $rows): array
    {
        $demand = (float) array_sum(array_column($rows, 'demand_pcs'));
        $daily = (float) array_sum(array_column($rows, 'daily_output_pcs'));
        $cumulative = (float) array_sum(array_column($rows, 'cumulative_output_pcs'));

        $demandTon = (float) array_sum(array_column($rows, 'demand_ton'));
        $dailyTon = (float) array_sum(array_column($rows, 'output_ton'));
        $cumulativeTon = (float) array_sum(array_column($rows, 'cumulative_ton'));

        $wasteKg = (float) array_sum(array_column($rows, 'waste_kg'));
        $bpKg = (float) array_sum(array_column($rows, 'bp_kg'));

        return [
            'order_count' => count($rows),
            'demand_pcs' => $demand,
            'demand_ton' => $demandTon,
            'daily_output_pcs' => $daily,
            'daily_output_ton' => $dailyTon,
            'cumulative_output_pcs' => $cumulative,
            'cumulative_output_ton' => $cumulativeTon,
            'remaining_pcs' => $demand - $cumulative,
            'remaining_ton' => $demandTon - $cumulativeTon,
            'waste_kg' => $wasteKg,
            'waste_pct' => $bpKg > 0
                ? ($wasteKg / $bpKg) * 100
                : 0,
        ];
    }
}
