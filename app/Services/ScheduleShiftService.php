<?php

namespace App\Services;

use App\Models\PublicHoliday;
use App\Models\WorkingWeekday;
use Carbon\Carbon;

class ScheduleShiftService
{
    private array $workingDayNumbers = [];
    private array $holidayDates = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $this->workingDayNumbers = WorkingWeekday::active()
            ->where('is_working_day', true)
            ->pluck('day_number')
            ->map(fn($day) => (int) $day)
            ->unique()
            ->values()
            ->all();

        $this->holidayDates = PublicHoliday::active()
            ->pluck('holiday_date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values()
            ->all();
    }

    public function isWorkingDay(Carbon $date): bool
    {
        return in_array(
            $date->isoWeekday(),
            $this->workingDayNumbers,
            true
        ) && !in_array(
            $date->toDateString(),
            $this->holidayDates,
            true
        );
    }

    public function isNonWorkingDay(Carbon $date): bool
    {
        return !$this->isWorkingDay($date);
    }

    public function workingDayDifference(Carbon $from, Carbon $to): int
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();

        if ($from->equalTo($to)) {
            return 0;
        }

        $direction = $to->greaterThan($from) ? 1 : -1;

        $current = $from->copy()->addDays($direction);
        $count = 0;

        while (
            $direction > 0
            ? $current->lte($to)
            : $current->gte($to)
        ) {
            if ($this->isWorkingDay($current)) {
                $count++;
            }

            $current->addDays($direction);
        }

        return $count * $direction;
    }

    public function addWorkingDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();
        $direction = $days >= 0 ? 1 : -1;
        $remaining = abs($days);

        while ($remaining > 0) {
            $result->addDays($direction);

            if ($this->isWorkingDay($result)) {
                $remaining--;
            }
        }

        return $result;
    }

    public function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy()->startOfDay();
        $lastDate = $end->copy()->startOfDay();

        while ($current->lte($lastDate)) {
            if ($this->isWorkingDay($current)) {
                $count++;
            }

            $current->addDay();
        }

        return $count;
    }

    public function previewShift(array $schedules, int $shiftDays): array
    {
        $result = [];

        foreach ($schedules as $schedule) {
            $newStart = $this->addWorkingDays(
                Carbon::parse($schedule['start_date']),
                $shiftDays
            );

            $newEnd = $this->addWorkingDays(
                Carbon::parse($schedule['end_date']),
                $shiftDays
            );

            $result[] = [
                'id' => $schedule['id'],
                'order_no' => $schedule['order_no'],
                'old_start_date' => $schedule['start_date'],
                'old_end_date' => $schedule['end_date'],
                'new_start_date' => $newStart->toDateString(),
                'new_end_date' => $newEnd->toDateString(),
            ];
        }

        return $result;
    }
}
