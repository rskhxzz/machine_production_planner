@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@push('styles')
<style>
    .settings-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-width: 1000px;
        min-width: 0;
    }

    .settings-section {
        min-width: 0;
    }

    .settings-section-description {
        margin-top: 3px;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 400;
    }

    .settings-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .settings-form-grid.three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .settings-form-grid .form-group {
        min-width: 0;
        margin-bottom: 0;
    }

    .settings-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .settings-weekdays {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }

    .settings-weekday {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 8px;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: #fff;
        cursor: pointer;
        transition: .15s ease;
        min-width: 0;
    }

    .settings-weekday:has(input:checked) {
        background: #F0FDF4;
        border-color: #86EFAC;
        color: var(--green-dark);
    }

    .settings-weekday input {
        width: 15px;
        height: 15px;
        accent-color: var(--green);
        cursor: pointer;
        flex-shrink: 0;
    }

    .settings-weekday span {
        font-size: 12px;
        font-weight: 500;
    }

    .settings-table {
        min-width: 650px;
    }

    .settings-table td {
        vertical-align: middle;
    }

    .settings-count {
        color: var(--text-muted);
        font-size: 11px;
    }

    .settings-mobile-list {
        display: none;
    }

    .settings-mobile-item {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
    }

    .settings-mobile-item:last-child {
        border-bottom: none;
    }

    .settings-mobile-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .settings-mobile-name {
        font-size: 13px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .settings-mobile-description {
        color: var(--text-muted);
        font-size: 11px;
        margin-top: 4px;
        overflow-wrap: anywhere;
    }

    .settings-mobile-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
    }

    .settings-mobile-actions .btn {
        flex: 1;
        justify-content: center;
    }

    .settings-holiday-description {
        max-width: 220px;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .settings-machine-form {
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .settings-modal-form {
        width: 100%;
    }

    .settings-modal-form .form-group:last-child {
        margin-bottom: 0;
    }

    .settings-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .settings-modal .modal-body {
        padding: 18px 20px;
    }

    .settings-pagination {
        padding: 8px 14px;
    }

    @media (max-width: 900px) {
        .settings-weekdays {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .settings-form-grid.three {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {

        .settings-form-grid,
        .settings-form-grid.three {
            grid-template-columns: 1fr;
        }

        .settings-weekdays {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .settings-table-container {
            display: none;
        }

        .settings-mobile-list {
            display: block;
        }

        .settings-section .card-header {
            align-items: flex-start;
        }

        .settings-section .card-body {
            padding: 14px;
        }

        .settings-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 420px) {
        .settings-weekdays {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .settings-modal-actions {
            flex-direction: column;
        }

        .settings-modal-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
@php
$shiftsPerDayValue = $settings['shifts_per_day']->setting_value ?? 3;
$targetShiftsValue = $settings['target_shifts_per_day']->setting_value ?? 2;

if (is_array($shiftsPerDayValue)) {
$shiftsPerDayValue = $shiftsPerDayValue['value'] ?? 3;
}

if (is_array($targetShiftsValue)) {
$targetShiftsValue = $targetShiftsValue['value'] ?? 2;
}

$selectedWorkingDays = old(
'working_days',
$weekdays->where('is_working_day', true)->pluck('day_number')->all()
);

$selectedWorkingDays = array_map('strval', $selectedWorkingDays);
@endphp

<div class="settings-page">

    {{-- Pengaturan Shift --}}
    <section class="card settings-section">
        <div class="card-header">
            <div>
                <div class="card-title">Pengaturan Shift</div>
                <div class="settings-section-description">
                    Konfigurasi jumlah shift operasional dan target shift OS.
                </div>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('settings.general.update') }}">
                @csrf

                <div class="settings-form-grid">
                    <div class="form-group">
                        <label class="form-label required">
                            Jumlah Shift/Hari
                        </label>

                        <input
                            type="number"
                            name="shifts_per_day"
                            value="{{ old('shifts_per_day', $shiftsPerDayValue) }}"
                            class="form-control {{ $errors->has('shifts_per_day') ? 'is-invalid' : '' }}"
                            min="1"
                            max="4"
                            required>

                        @error('shifts_per_day')
                        <div class="form-error">{{ $message }}</div>
                        @enderror

                        <div class="form-hint">
                            Digunakan untuk menghitung MP/Day.
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">
                            Target Shift OS
                        </label>

                        <input
                            type="number"
                            name="target_shifts_per_day"
                            value="{{ old('target_shifts_per_day', $targetShiftsValue) }}"
                            class="form-control {{ $errors->has('target_shifts_per_day') ? 'is-invalid' : '' }}"
                            min="1"
                            max="4"
                            required>

                        @error('target_shifts_per_day')
                        <div class="form-error">{{ $message }}</div>
                        @enderror

                        <div class="form-hint">
                            Target jumlah shift OS per hari.
                        </div>
                    </div>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Konfigurasi Hari Kerja --}}
    <section class="card settings-section">
        <div class="card-header">
            <div>
                <div class="card-title">Konfigurasi Hari Kerja</div>
                <div class="settings-section-description">
                    Pilih hari yang dihitung sebagai hari kerja untuk pergeseran jadwal.
                </div>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('settings.weekdays.update') }}">
                @csrf

                <div class="settings-weekdays">
                    @foreach($weekdays as $weekday)
                    <label class="settings-weekday">
                        <input
                            type="checkbox"
                            name="working_days[]"
                            value="{{ $weekday->day_number }}"
                            {{ in_array((string) $weekday->day_number, $selectedWorkingDays, true) ? 'checked' : '' }}>

                        <span>{{ $weekday->day_name }}</span>
                    </label>
                    @endforeach
                </div>

                @error('working_days')
                <div class="form-error" style="margin-top:8px;">
                    {{ $message }}
                </div>
                @enderror

                <div class="settings-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Simpan Hari Kerja
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Hari Libur --}}
    <section class="card settings-section">
        <div class="card-header">
            <div>
                <div class="card-title">Hari Libur</div>
                <div class="settings-section-description">
                    Kelola tanggal libur yang tidak dihitung sebagai hari kerja.
                </div>
            </div>

            <span class="badge badge-blue">
                {{ number_format($holidays->total()) }} hari
            </span>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('settings.holidays.store') }}">

                @csrf

                <div class="settings-form-grid three">
                    <div class="form-group">
                        <label class="form-label required">Tanggal</label>

                        <input
                            type="date"
                            name="holiday_date"
                            value="{{ old('holiday_date') }}"
                            class="form-control {{ $errors->has('holiday_date') ? 'is-invalid' : '' }}"
                            required>

                        @error('holiday_date')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Nama Hari Libur</label>

                        <input
                            type="text"
                            name="holiday_name"
                            value="{{ old('holiday_name') }}"
                            class="form-control {{ $errors->has('holiday_name') ? 'is-invalid' : '' }}"
                            placeholder="Contoh: Hari Raya Idul Fitri"
                            maxlength="150"
                            required>

                        @error('holiday_name')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan</label>

                        <input
                            type="text"
                            name="description"
                            value="{{ old('description') }}"
                            class="form-control"
                            placeholder="Opsional"
                            maxlength="500">

                        @error('description')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        + Tambah Hari Libur
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container settings-table-container">
            @if($holidays->count() > 0)
            <table class="settings-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Hari Libur</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($holidays as $holiday)
                    <tr>
                        <td class="nowrap font-semibold">
                            {{ $holiday->holiday_date->format('d M Y') }}
                        </td>

                        <td>
                            {{ $holiday->holiday_name }}
                        </td>

                        <td class="text-muted settings-holiday-description">
                            {{ $holiday->description ?? '-' }}
                        </td>

                        <td>
                            <form
                                id="delete-holiday-{{ $holiday->id }}"
                                method="POST"
                                action="{{ route('settings.holidays.destroy', $holiday) }}">
                                @csrf
                                @method('DELETE')
                            </form>

                            <button
                                type="button"
                                onclick="confirmDelete('delete-holiday-{{ $holiday->id }}')"
                                class="btn btn-sm btn-danger">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <div class="empty-state-icon">📅</div>
                <h3>Belum ada hari libur</h3>
                <p>Tambahkan tanggal hari libur melalui formulir di atas.</p>
            </div>
            @endif
        </div>

        <div class="settings-mobile-list">
            @forelse($holidays as $holiday)
            <div class="settings-mobile-item">
                <div class="settings-mobile-top">
                    <div style="min-width:0;">
                        <div class="settings-mobile-name">
                            {{ $holiday->holiday_name }}
                        </div>

                        <div class="settings-mobile-description">
                            {{ $holiday->holiday_date->format('d M Y') }}
                        </div>

                        @if($holiday->description)
                        <div class="settings-mobile-description">
                            {{ $holiday->description }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="settings-mobile-actions">
                    <form
                        id="mobile-delete-holiday-{{ $holiday->id }}"
                        method="POST"
                        action="{{ route('settings.holidays.destroy', $holiday) }}">
                        @csrf
                        @method('DELETE')
                    </form>

                    <button
                        type="button"
                        onclick="confirmDelete('mobile-delete-holiday-{{ $holiday->id }}')"
                        class="btn btn-sm btn-danger">
                        Hapus Hari Libur
                    </button>
                </div>
            </div>
            @empty
            <div class="empty-state" style="padding:20px;">
                Belum ada hari libur.
            </div>
            @endforelse
        </div>

        <div class="settings-pagination">
            {{ $holidays->links('components.pagination') }}
        </div>
    </section>

    {{-- Master Mesin --}}
    <section class="card settings-section">
        <div class="card-header">
            <div>
                <div class="card-title">Master Mesin</div>
                <div class="settings-section-description">
                    Kelola daftar mesin yang digunakan dalam perencanaan produksi.
                </div>
            </div>

            <span class="badge badge-blue">
                {{ number_format($machines->count()) }} mesin
            </span>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('settings.machines.store') }}"
                class="settings-machine-form">

                @csrf

                <div class="settings-form-grid three">
                    <div class="form-group">
                        <label class="form-label required">Kode Mesin</label>

                        <input
                            type="text"
                            name="machine_code"
                            value="{{ old('machine_code') }}"
                            class="form-control {{ $errors->has('machine_code') ? 'is-invalid' : '' }}"
                            placeholder="Contoh: CS17"
                            maxlength="20"
                            required>

                        @error('machine_code')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Nama Mesin</label>

                        <input
                            type="text"
                            name="machine_name"
                            value="{{ old('machine_name') }}"
                            class="form-control {{ $errors->has('machine_name') ? 'is-invalid' : '' }}"
                            placeholder="Nama mesin"
                            maxlength="100"
                            required>

                        @error('machine_name')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Tipe Mesin</label>

                        <select
                            name="machine_type"
                            class="form-control {{ $errors->has('machine_type') ? 'is-invalid' : '' }}"
                            required>

                            <option value="AUTO" {{ old('machine_type') === 'AUTO' ? 'selected' : '' }}>
                                AUTO
                            </option>

                            <option value="SEMI" {{ old('machine_type') === 'SEMI' ? 'selected' : '' }}>
                                SEMI
                            </option>
                        </select>

                        @error('machine_type')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        + Tambah Mesin
                    </button>
                </div>
            </form>
        </div>

        @if($machines->count() > 0)

        <div class="table-container settings-table-container">
            <table class="settings-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Mesin</th>
                        <th>Tipe</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($machines as $machine)
                    <tr>
                        <td class="font-semibold">
                            {{ $machine->machine_code }}
                        </td>

                        <td>
                            {{ $machine->machine_name }}
                        </td>

                        <td>
                            <span class="badge {{ $machine->machine_type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                                {{ $machine->machine_type }}
                            </span>
                        </td>

                        <td class="text-muted">
                            {{ $machine->description ?? '-' }}
                        </td>

                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-secondary edit-machine-btn"
                                data-machine-id="{{ $machine->id }}"
                                data-machine-name="{{ $machine->machine_name }}"
                                data-machine-type="{{ $machine->machine_type }}"
                                data-machine-description="{{ $machine->description ?? '' }}"
                                data-update-url="{{ route('settings.machines.update', $machine) }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="settings-mobile-list">
            @foreach($machines as $machine)
            <div class="settings-mobile-item">
                <div class="settings-mobile-top">
                    <div style="min-width:0;">
                        <div class="settings-mobile-name">
                            {{ $machine->machine_code }} · {{ $machine->machine_name }}
                        </div>

                        <div style="margin-top:5px;">
                            <span class="badge {{ $machine->machine_type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                                {{ $machine->machine_type }}
                            </span>
                        </div>

                        <div class="settings-mobile-description">
                            {{ $machine->description ?? 'Tidak ada deskripsi' }}
                        </div>
                    </div>
                </div>

                <div class="settings-mobile-actions">
                    <button
                        type="button"
                        class="btn btn-sm btn-secondary edit-machine-btn"
                        data-machine-id="{{ $machine->id }}"
                        data-machine-name="{{ $machine->machine_name }}"
                        data-machine-type="{{ $machine->machine_type }}"
                        data-machine-description="{{ $machine->description ?? '' }}"
                        data-update-url="{{ route('settings.machines.update', $machine) }}">
                        Edit Mesin
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        @else
        <div class="empty-state">
            <div class="empty-state-icon">🏭</div>
            <h3>Belum ada master mesin</h3>
            <p>Tambahkan mesin melalui formulir di atas.</p>
        </div>
        @endif
    </section>

</div>

{{-- Modal Edit Mesin --}}
<div class="modal-backdrop" id="editMachineModal">
    <div class="modal settings-modal">
        <div class="modal-header">
            <div>
                <div class="modal-title">Edit Master Mesin</div>
                <div class="settings-section-description">
                    Perbarui informasi mesin yang dipilih.
                </div>
            </div>

            <button
                type="button"
                class="modal-close"
                id="closeEditMachineModal"
                aria-label="Tutup">
                ✕
            </button>
        </div>

        <form id="editMachineForm" method="POST" class="settings-modal-form">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Mesin</label>

                    <input
                        type="text"
                        name="machine_name"
                        id="editMachineName"
                        class="form-control"
                        maxlength="100"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Tipe Mesin</label>

                    <select
                        name="machine_type"
                        id="editMachineType"
                        class="form-control"
                        required>

                        <option value="AUTO">AUTO</option>
                        <option value="SEMI">SEMI</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>

                    <textarea
                        name="description"
                        id="editMachineDesc"
                        class="form-control"
                        rows="3"
                        maxlength="500"
                        placeholder="Keterangan mesin (opsional)"></textarea>
                </div>
            </div>

            <div class="modal-footer settings-modal-actions">
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <button
                    type="button"
                    class="btn btn-secondary"
                    id="cancelEditMachineModal">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const modal = document.getElementById('editMachineModal');
        const form = document.getElementById('editMachineForm');

        if (!modal || !form) {
            return;
        }

        function closeEditModal() {
            closeModal('editMachineModal');
        }

        document.addEventListener('click', function(event) {
            const editButton = event.target.closest('.edit-machine-btn');

            if (editButton) {
                form.action = editButton.dataset.updateUrl;

                document.getElementById('editMachineName').value =
                    editButton.dataset.machineName || '';

                document.getElementById('editMachineType').value =
                    editButton.dataset.machineType || 'AUTO';

                document.getElementById('editMachineDesc').value =
                    editButton.dataset.machineDescription || '';

                openModal('editMachineModal');
            }
        });

        document.getElementById('closeEditMachineModal')
            ?.addEventListener('click', closeEditModal);

        document.getElementById('cancelEditMachineModal')
            ?.addEventListener('click', closeEditModal);

        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeEditModal();
            }
        });
    })();
</script>
@endpush