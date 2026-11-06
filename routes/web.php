<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManpowerController;
use App\Http\Controllers\MesProductionController;
use App\Http\Controllers\ProductionRecapController;
use App\Http\Controllers\ProductionScheduleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama langsung menuju Manpower
Route::get('/', function () {
    return redirect()->route('manpower.index');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Manpower
Route::prefix('manpower')->name('manpower.')->group(function () {
    Route::get('/', [ManpowerController::class, 'index'])->name('index');

    Route::post('/override', [ManpowerController::class, 'updateOverride'])
        ->name('override.update');

    Route::post('/override/reset', [ManpowerController::class, 'resetOverride'])
        ->name('override.reset');

    Route::post('/params', [ManpowerController::class, 'updateDailyParams'])
        ->name('params.update');

    Route::post('/realization', [ManpowerController::class, 'updateRealization'])
        ->name('realization.update');
});

// Jadwal Produksi
Route::prefix('schedules')->name('schedules.')->group(function () {
    // Halaman utama dan tambah jadwal
    Route::get('/', [ProductionScheduleController::class, 'index'])
        ->name('index');

    Route::get('/create', [ProductionScheduleController::class, 'create'])
        ->name('create');

    Route::post('/', [ProductionScheduleController::class, 'store'])
        ->name('store');

    // Pengaturan shift
    Route::get('/shift/preview', [ProductionScheduleController::class, 'shiftPreview'])
        ->name('shift.preview');

    Route::post('/shift/apply', [ProductionScheduleController::class, 'shiftApply'])
        ->name('shift.apply');

    // Import jadwal
    Route::get('/import/form', [ProductionScheduleController::class, 'importForm'])
        ->name('import');

    Route::post('/import/preview', [ProductionScheduleController::class, 'importPreview'])
        ->name('import.preview');

    Route::post('/import/confirm', [ProductionScheduleController::class, 'importConfirm'])
        ->name('import.confirm');

    Route::get('/import/template', [ProductionScheduleController::class, 'downloadTemplate'])
        ->name('import.template');

    // Detail, edit, dan riwayat jadwal
    Route::get('/{schedule}/history', [ProductionScheduleController::class, 'history'])
        ->name('history');

    Route::get('/{schedule}/edit', [ProductionScheduleController::class, 'edit'])
        ->name('edit');

    Route::get('/{schedule}', [ProductionScheduleController::class, 'show'])
        ->name('show');

    Route::put('/{schedule}', [ProductionScheduleController::class, 'update'])
        ->name('update');

    Route::delete('/{schedule}', [ProductionScheduleController::class, 'destroy'])
        ->name('destroy');
});

// Aktual Produksi (MES)
Route::prefix('actual-production')->name('mes.')->group(function () {
    Route::get('/', [MesProductionController::class, 'index'])
        ->name('index');

    Route::get('/import', [MesProductionController::class, 'importForm'])
        ->name('import');

    Route::post('/import/preview', [MesProductionController::class, 'importPreview'])
        ->name('import.preview');

    Route::post('/import/confirm', [MesProductionController::class, 'importConfirm'])
        ->name('import.confirm');

    Route::get('/history', [MesProductionController::class, 'importHistory'])
        ->name('history');
});

// Rekap Produksi
Route::prefix('production-recap')->name('recap.')->group(function () {
    Route::get('/', [ProductionRecapController::class, 'index'])
        ->name('index');
});

// Pengaturan
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])
        ->name('index');

    Route::post('/general', [SettingController::class, 'updateSettings'])
        ->name('general.update');

    Route::post('/weekdays', [SettingController::class, 'updateWeekdays'])
        ->name('weekdays.update');

    Route::post('/holidays', [SettingController::class, 'storeHoliday'])
        ->name('holidays.store');

    Route::delete('/holidays/{publicHoliday}', [SettingController::class, 'destroyHoliday'])
        ->name('holidays.destroy');

    Route::post('/machines', [SettingController::class, 'storeMachine'])
        ->name('machines.store');

    Route::put('/machines/{machine}', [SettingController::class, 'updateMachine'])
        ->name('machines.update');
});
