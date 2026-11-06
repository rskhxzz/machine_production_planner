@extends('layouts.app')

@section('title', 'Edit Jadwal Produksi')
@section('page-title', 'Edit Jadwal Produksi')

@push('styles')
<style>
    .schedule-edit-page {
        max-width: 820px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .schedule-edit-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .schedule-edit-description {
        color: var(--text-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .schedule-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .schedule-edit-grid .form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    .schedule-edit-section {
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .schedule-edit-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .schedule-edit-section-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 12px;
    }

    .schedule-edit-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    @media (max-width: 600px) {
        .schedule-edit-page {
            max-width: none;
        }

        .schedule-edit-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .schedule-edit-actions {
            flex-direction: column;
        }

        .schedule-edit-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-edit-page">

    <div class="schedule-edit-toolbar">
        <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
            Kembali ke Jadwal
        </a>

        <a href="{{ route('schedules.show', $schedule) }}" class="btn btn-secondary btn-sm">
            Detail Jadwal
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Edit Jadwal Produksi</div>
                <div class="schedule-edit-description" style="margin-top:3px;">
                    {{ $schedule->machine->machine_code }} · {{ $schedule->order_no }}
                </div>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('schedules.update', $schedule) }}">
                @csrf
                @method('PUT')

                <div class="schedule-edit-section">
                    <div class="schedule-edit-section-title">
                        Informasi Mesin dan Order
                    </div>

                    <div class="schedule-edit-grid">
                        <div class="form-group">
                            <label class="form-label required">Mesin</label>

                            <select
                                name="machine_id"
                                class="form-control {{ $errors->has('machine_id') ? 'is-invalid' : '' }}"
                                required>

                                <option value="">Pilih Mesin</option>

                                @foreach($machines as $machine)
                                <option
                                    value="{{ $machine->id }}"
                                    {{ old('machine_id', $schedule->machine_id) == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->machine_code }} - {{ $machine->machine_name }}
                                    ({{ $machine->machine_type }})
                                </option>
                                @endforeach
                            </select>

                            @error('machine_id')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Urutan (Sequence)</label>

                            <input
                                type="number"
                                name="schedule_sequence"
                                value="{{ old('schedule_sequence', $schedule->schedule_sequence) }}"
                                class="form-control {{ $errors->has('schedule_sequence') ? 'is-invalid' : '' }}"
                                min="1">

                            @error('schedule_sequence')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Order Number</label>

                            <input
                                type="text"
                                name="order_no"
                                value="{{ old('order_no', $schedule->order_no) }}"
                                class="form-control {{ $errors->has('order_no') ? 'is-invalid' : '' }}"
                                maxlength="100"
                                required>

                            @error('order_no')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Note</label>

                            <input
                                type="text"
                                name="note"
                                value="{{ old('note', $schedule->note) }}"
                                class="form-control {{ $errors->has('note') ? 'is-invalid' : '' }}"
                                maxlength="500"
                                placeholder="Keterangan tambahan">

                            @error('note')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="schedule-edit-section">
                    <div class="schedule-edit-section-title">
                        Periode Produksi
                    </div>

                    <div class="schedule-edit-grid">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Mulai</label>

                            <input
                                type="date"
                                name="start_date"
                                value="{{ old('start_date', $schedule->start_date?->format('Y-m-d')) }}"
                                class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                                required>

                            @error('start_date')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Tanggal Selesai</label>

                            <input
                                type="date"
                                name="end_date"
                                value="{{ old('end_date', $schedule->end_date?->format('Y-m-d')) }}"
                                class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                required>

                            @error('end_date')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="schedule-edit-section">
                    <div class="schedule-edit-section-title">
                        Jenis Jadwal
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Jenis Jadwal</label>

                        <select
                            name="is_special"
                            id="is_special"
                            class="form-control"
                            required>

                            <option
                                value="0"
                                {{ old('is_special', $schedule->is_special ? '1' : '0') == '0' ? 'selected' : '' }}>
                                Jadwal Reguler
                            </option>

                            <option
                                value="1"
                                {{ old('is_special', $schedule->is_special ? '1' : '0') == '1' ? 'selected' : '' }}>
                                Jadwal Khusus (Hari Libur / Weekend)
                            </option>
                        </select>

                        @error('is_special')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                    $selectedSpecialShifts = array_map(
                    'intval',
                    old('special_shifts', $schedule->special_shifts ?? [])
                    );
                    @endphp

                    <div
                        id="special-shifts-container"
                        style="{{ old('is_special', $schedule->is_special ? '1' : '0') == '1' ? '' : 'display:none;' }}; margin-top:16px;">

                        <label class="form-label required">
                            Shift yang Beroperasi
                        </label>

                        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:10px;">
                            @for($shift = 1; $shift <= $shiftsPerDay; $shift++)
                                <label style="display:flex;align-items:center;gap:7px;cursor:pointer;">
                                <input
                                    type="checkbox"
                                    name="special_shifts[]"
                                    value="{{ $shift }}"
                                    {{ in_array($shift, $selectedSpecialShifts) ? 'checked' : '' }}>

                                Shift {{ $shift }}
                                </label>
                                @endfor
                        </div>

                        @error('special_shifts')
                        <div class="form-error">{{ $message }}</div>
                        @enderror

                        @error('special_shifts.*')
                        <div class="form-error">{{ $message }}</div>
                        @enderror

                        <div class="form-hint">
                            Pilih shift yang benar-benar beroperasi pada jadwal khusus ini.
                        </div>
                    </div>
                </div>

                <div class="schedule-edit-section">
                    <div class="schedule-edit-section-title">
                        Informasi Produksi
                    </div>

                    <div class="schedule-edit-grid">
                        <div class="form-group">
                            <label class="form-label">MT</label>

                            <input
                                type="number"
                                name="mt"
                                value="{{ old('mt', $schedule->mt) }}"
                                class="form-control {{ $errors->has('mt') ? 'is-invalid' : '' }}"
                                step="0.001"
                                min="0">

                            @error('mt')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Capacity</label>

                            <input
                                type="number"
                                name="capacity"
                                value="{{ old('capacity', $schedule->capacity) }}"
                                class="form-control {{ $errors->has('capacity') ? 'is-invalid' : '' }}"
                                step="0.001"
                                min="0">

                            @error('capacity')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Qty PCS</label>

                            <input
                                type="number"
                                name="qty_pcs"
                                value="{{ old('qty_pcs', $schedule->qty_pcs) }}"
                                class="form-control {{ $errors->has('qty_pcs') ? 'is-invalid' : '' }}"
                                step="0.000001"
                                min="0">

                            @error('qty_pcs')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">MP Ideal/Shift</label>

                            <input
                                type="number"
                                name="mp_ideal_per_shift"
                                value="{{ old('mp_ideal_per_shift', $schedule->mp_ideal_per_shift) }}"
                                class="form-control {{ $errors->has('mp_ideal_per_shift') ? 'is-invalid' : '' }}"
                                step="0.01"
                                min="0">

                            @error('mp_ideal_per_shift')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                            <div class="form-hint">
                                Kosongkan jika kebutuhan manpower belum ditentukan.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="schedule-edit-actions">
                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>

                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection