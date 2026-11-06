<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\PlannerSetting;
use App\Models\PublicHoliday;
use App\Models\WorkingWeekday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = PlannerSetting::active()
            ->get()
            ->keyBy('setting_key');

        $weekdays = WorkingWeekday::active()
            ->orderBy('day_number')
            ->get();

        $holidays = PublicHoliday::active()
            ->orderBy('holiday_date')
            ->paginate(20, ['*'], 'holidays_page');

        $machines = Machine::active()
            ->orderBy('machine_code')
            ->get();

        return view('settings.index', compact(
            'settings',
            'weekdays',
            'holidays',
            'machines'
        ));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'shifts_per_day' => [
                'required',
                'integer',
                'min:1',
                'max:4',
            ],
            'target_shifts_per_day' => [
                'required',
                'integer',
                'min:1',
                'max:4',
            ],
        ]);

        DB::transaction(function () use ($validated) {
            PlannerSetting::setValue(
                'shifts_per_day',
                (int) $validated['shifts_per_day'],
                'Jumlah shift operasional dalam satu hari'
            );

            PlannerSetting::setValue(
                'target_shifts_per_day',
                (int) $validated['target_shifts_per_day'],
                'Target shift manpower OS dalam satu hari'
            );
        });

        return redirect()
            ->route('settings.index')
            ->with('success', 'Pengaturan shift berhasil disimpan.');
    }

    public function updateWeekdays(Request $request)
    {
        $validated = $request->validate([
            'working_days' => ['nullable', 'array'],
            'working_days.*' => [
                'required',
                'integer',
                'between:1,7',
            ],
        ]);

        $workingDays = array_map(
            'intval',
            $validated['working_days'] ?? []
        );

        DB::transaction(function () use ($workingDays) {
            $weekdays = WorkingWeekday::active()->get();

            foreach ($weekdays as $weekday) {
                $weekday->update([
                    'is_working_day' => in_array(
                        (int) $weekday->day_number,
                        $workingDays,
                        true
                    ),
                ]);
            }
        });

        return redirect()
            ->route('settings.index')
            ->with('success', 'Konfigurasi hari kerja berhasil disimpan.');
    }

    public function storeHoliday(Request $request)
    {
        $validated = $request->validate([
            'holiday_date' => ['required', 'date'],
            'holiday_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = PublicHoliday::active()
            ->where('holiday_date', $validated['holiday_date'])
            ->first();

        if ($existing) {
            return redirect()
                ->route('settings.index')
                ->with(
                    'error',
                    'Tanggal libur ' . $validated['holiday_date'] . ' sudah terdaftar.'
                );
        }

        PublicHoliday::create($validated);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroyHoliday(PublicHoliday $publicHoliday)
    {
        if ($publicHoliday->is_deleted) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Data hari libur sudah tidak aktif.');
        }

        $publicHoliday->update([
            'is_deleted' => true,
        ]);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    public function updateMachine(Request $request, Machine $machine)
    {
        if ($machine->is_deleted) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Data mesin sudah tidak aktif.');
        }

        $validated = $request->validate([
            'machine_name' => [
                'required',
                'string',
                'max:100',
            ],
            'machine_type' => [
                'required',
                'in:AUTO,SEMI',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $machine->update($validated);

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                "Mesin {$machine->machine_code} berhasil diperbarui."
            );
    }

    public function storeMachine(Request $request)
    {
        $validated = $request->validate([
            'machine_code' => [
                'required',
                'string',
                'max:20',
                'unique:machines,machine_code',
            ],
            'machine_name' => [
                'required',
                'string',
                'max:100',
            ],
            'machine_type' => [
                'required',
                'in:AUTO,SEMI',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        Machine::create($validated);

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                "Mesin {$validated['machine_code']} berhasil ditambahkan."
            );
    }
}
