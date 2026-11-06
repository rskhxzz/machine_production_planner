@extends('layouts.app')

@section('title', 'Tambah Jadwal Produksi')
@section('page-title', 'Tambah Jadwal Produksi')

@push('styles')
<style>
    .schedule-create-page {
        max-width: 820px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .schedule-create-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .schedule-create-grid .form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    .schedule-create-section {
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .schedule-create-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .schedule-create-section-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 12px;
    }

    .schedule-create-description {
        color: var(--text-muted);
        font-size: 12px;
        line-height: 1.6;
        margin-top: 3px;
    }

    .schedule-create-note {
        padding: 11px 13px;
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-radius: 7px;
        color: #166534;
        font-size: 12px;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .schedule-create-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    @media (max-width: 600px) {
        .schedule-create-page {
            max-width: none;
        }

        .schedule-create-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .schedule-create-actions {
            flex-direction: column;
        }

        .schedule-create-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-create-page">

    <div>
        <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
            Kembali ke Jadwal
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Form Tambah Jadwal</div>
                <div class="schedule-create-description">
                    Masukkan informasi mesin, order, periode, dan kebutuhan manpower.
                </div>
            </div>
        </div>

        <div class="card-body">

            <div class="schedule-create-note">
                <strong>Informasi:</strong>
                Order dengan nomor <strong>STOP</strong> dapat digunakan untuk menandai
                mesin dalam kondisi berhenti.
            </div>

            <form method="POST" action="{{ route('schedules.store') }}">
                @csrf

                <div class="schedule-create-section">
                    <div class="schedule-create-section-title">
                        Informasi Mesin dan Order
                    </div>

                    <div class="schedule-create-grid">
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
                                    {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
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
                                value="{{ old('schedule_sequence') }}"
                                class="form-control {{ $errors->has('schedule_sequence') ? 'is-invalid' : '' }}"
                                min="1"
                                placeholder="Contoh: 1">

                            @error('schedule_sequence')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Order Number</label>

                            <input
                                type="text"
                                name="order_no"
                                value="{{ old('order_no') }}"
                                class="form-control {{ $errors->has('order_no') ? 'is-invalid' : '' }}"
                                required
                                maxlength="100"
                                placeholder="Contoh: 4790011000-1">

                            @error('order_no')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                            <div class="form-hint">
                                Masukkan STOP apabila mesin berhenti berproduksi.
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Note</label>

                            <input
                                type="text"
                                name="note"
                                value="{{ old('note') }}"
                                class="form-control {{ $errors->has('note') ? 'is-invalid' : '' }}"
                                maxlength="500"
                                placeholder="Keterangan tambahan">

                            @error('note')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="schedule-create-section">
                    <div class="schedule-create-section-title">
                        Periode Produksi
                    </div>

                    <div class="schedule-create-grid">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Mulai</label>

                            <input
                                type="date"
                                name="start_date"
                                value="{{ old('start_date') }}"
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
                                value="{{ old('end_date') }}"
                                class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                required>

                            @error('end_date')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="schedule-create-section">
                    <div class="schedule-create-section-title">
                        Jenis Jadwal
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Jenis Jadwal</label>

                        <select
                            name="is_special"
                            id="is_special"
                            class="form-control"
                            required>

                            <option value="0" {{ old('is_special', '0') == '0' ? 'selected' : '' }}>
                                Jadwal Reguler
                            </option>

                            <option value="1" {{ old('is_special') == '1' ? 'selected' : '' }}>
                                Jadwal Khusus (Hari Libur / Weekend)
                            </option>
                        </select>

                        @error('is_special')
                        <div class="form-error">{{ $message }}</div>
                        @enderror

                        <div class="form-hint">
                            Jadwal reguler mengikuti kalender hari kerja. Jadwal khusus
                            hanya dihitung ketika tanggal merupakan hari libur atau weekend.
                        </div>
                    </div>

                    <div
                        id="special-shifts-container"
                        style="{{ old('is_special') == '1' ? '' : 'display:none;' }}; margin-top:16px;">

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
                                    {{ in_array($shift, array_map('intval', old('special_shifts', []))) ? 'checked' : '' }}>

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

                <div class="schedule-create-section">
                    <div class="schedule-create-section-title">
                        Informasi Produksi
                    </div>

                    <div class="schedule-create-grid">
                        <div class="form-group">
                            <label class="form-label">MT</label>

                            <input
                                type="number"
                                name="mt"
                                value="{{ old('mt') }}"
                                class="form-control {{ $errors->has('mt') ? 'is-invalid' : '' }}"
                                step="0.001"
                                min="0"
                                placeholder="0.000">

                            @error('mt')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Capacity</label>

                            <input
                                type="number"
                                name="capacity"
                                value="{{ old('capacity') }}"
                                class="form-control {{ $errors->has('capacity') ? 'is-invalid' : '' }}"
                                step="0.001"
                                min="0"
                                placeholder="0.000">

                            @error('capacity')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Qty PCS</label>

                            <input
                                type="number"
                                name="qty_pcs"
                                value="{{ old('qty_pcs') }}"
                                class="form-control {{ $errors->has('qty_pcs') ? 'is-invalid' : '' }}"
                                step="0.000001"
                                min="0"
                                placeholder="0">

                            @error('qty_pcs')
                            <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">MP Ideal/Shift</label>

                            <input
                                type="number"
                                name="mp_ideal_per_shift"
                                value="{{ old('mp_ideal_per_shift') }}"
                                class="form-control {{ $errors->has('mp_ideal_per_shift') ? 'is-invalid' : '' }}"
                                step="0.01"
                                min="0"
                                placeholder="Contoh: 8.00">

                            @error('mp_ideal_per_shift')
                            <div class="form-error">{{ $message }}</div>
                            @enderror

                            <div class="form-hint">
                                Kosongkan jika kebutuhan manpower belum ditentukan.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="schedule-create-actions">
                    <button type="submit" class="btn btn-primary">
                        Simpan Jadwal
                    </button>

                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scheduleType = document.getElementById('is_special');
        const shiftContainer = document.getElementById('special-shifts-container');

        function toggleSpecialShifts() {
            const isSpecial = scheduleType.value === '1';

            shiftContainer.style.display = isSpecial ? 'block' : 'none';

            if (!isSpecial) {
                shiftContainer
                    .querySelectorAll('input[type="checkbox"]')
                    .forEach(input => input.checked = false);
            }
        }

        scheduleType.addEventListener('change', toggleSpecialShifts);

        toggleSpecialShifts();
    });
</script>
@endsection