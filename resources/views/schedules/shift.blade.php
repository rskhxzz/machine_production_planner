@extends('layouts.app')

@section('title', 'Geser Jadwal')
@section('page-title', 'Geser Jadwal Produksi')

@push('styles')
<style>
    .shift-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-width: 900px;
        min-width: 0;
    }

    .shift-toolbar {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .shift-info {
        padding: 12px 14px;
        border: 1px solid #BBF7D0;
        background: #F0FDF4;
        color: #166534;
        border-radius: 7px;
        font-size: 12px;
        line-height: 1.7;
        margin-bottom: 16px;
    }

    .shift-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .shift-form-grid .form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    .shift-direction {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
        color: var(--text-muted);
        font-size: 11px;
    }

    .shift-direction span {
        padding: 5px 9px;
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 5px;
    }

    .shift-preview-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .shift-preview-table {
        min-width: 650px;
    }

    .shift-mobile-list {
        display: none;
    }

    .shift-mobile-card {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
    }

    .shift-mobile-card:last-child {
        border-bottom: none;
    }

    .shift-order {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 10px;
        overflow-wrap: anywhere;
    }

    .shift-date-comparison {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .shift-date-item {
        padding: 10px;
        background: #F8FAFC;
        border-radius: 6px;
        min-width: 0;
    }

    .shift-date-label {
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 3px;
    }

    .shift-date-value {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
    }

    .shift-date-item.new {
        background: #F0FDF4;
    }

    .shift-date-item.new .shift-date-value {
        color: var(--green-dark);
    }

    .shift-confirm-box {
        padding: 14px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        color: #92400E;
        font-size: 12px;
        line-height: 1.7;
        border-radius: 7px;
        margin-bottom: 16px;
    }

    .shift-confirm-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .shift-page {
            max-width: none;
        }

        .shift-form-grid {
            grid-template-columns: 1fr;
        }

        .shift-table-container {
            display: none;
        }

        .shift-mobile-list {
            display: block;
        }

        .shift-confirm-actions {
            flex-direction: column;
        }

        .shift-confirm-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 420px) {
        .shift-toolbar .btn {
            flex: 1;
            justify-content: center;
        }

        .shift-date-comparison {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="shift-page">

    <div class="shift-toolbar">
        <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
            Kembali ke Jadwal
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Pengaturan Pergeseran Jadwal</span>
        </div>

        <div class="card-body">

            <div class="shift-info">
                <strong>Informasi:</strong>
                Pergeseran dihitung berdasarkan konfigurasi hari kerja dan hari libur.
                Semua jadwal pada mesin yang dipilih akan ikut bergeser.
            </div>

            <form method="GET" action="{{ route('schedules.shift.preview') }}">
                <div class="shift-form-grid">
                    <div class="form-group">
                        <label class="form-label required">Pilih Mesin</label>

                        <select name="machine_id" class="form-control" required>
                            <option value="">Pilih Mesin</option>

                            @foreach($machines as $machine)
                            <option
                                value="{{ $machine->id }}"
                                {{ $machineId == $machine->id ? 'selected' : '' }}>
                                {{ $machine->machine_code }} ({{ $machine->machine_type }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Jumlah Hari Kerja</label>

                        <input
                            type="number"
                            name="shift_days"
                            value="{{ $shiftDays }}"
                            class="form-control"
                            required
                            step="1"
                            placeholder="Contoh: 3 atau -2">

                        <div class="form-hint">
                            Masukkan bilangan bulat, bukan hari kalender.
                        </div>
                    </div>
                </div>

                <div class="shift-direction">
                    <span>Positif (+) = Maju</span>
                    <span>Negatif (-) = Mundur</span>
                    <span>0 = Tidak bergeser</span>
                </div>

                <div style="margin-top:16px;">
                    <button type="submit" class="btn btn-primary">
                        Lihat Preview Pergeseran
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($preview !== null && count($preview) > 0)

    <div class="card">
        <div class="card-header">
            <div class="shift-preview-heading" style="width:100%;">
                <span class="card-title">Preview Perubahan Jadwal</span>
                <span class="badge badge-green">
                    {{ count($preview) }} jadwal ditemukan
                </span>
            </div>
        </div>

        <div class="table-container shift-table-container">
            <table class="shift-preview-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Start Lama</th>
                        <th>Start Baru</th>
                        <th>End Lama</th>
                        <th>End Baru</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($preview as $item)
                    <tr>
                        <td class="font-semibold">
                            {{ $item['order_no'] }}
                        </td>

                        <td class="text-muted nowrap">
                            {{ \Carbon\Carbon::parse($item['old_start_date'])->format('d M Y') }}
                        </td>

                        <td class="text-green font-semibold nowrap">
                            {{ \Carbon\Carbon::parse($item['new_start_date'])->format('d M Y') }}
                        </td>

                        <td class="text-muted nowrap">
                            {{ \Carbon\Carbon::parse($item['old_end_date'])->format('d M Y') }}
                        </td>

                        <td class="text-green font-semibold nowrap">
                            {{ \Carbon\Carbon::parse($item['new_end_date'])->format('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="shift-mobile-list">
            @foreach($preview as $item)
            <div class="shift-mobile-card">
                <div class="shift-order">
                    {{ $item['order_no'] }}
                </div>

                <div class="shift-date-comparison">
                    <div class="shift-date-item">
                        <div class="shift-date-label">Start Lama</div>
                        <div class="shift-date-value">
                            {{ \Carbon\Carbon::parse($item['old_start_date'])->format('d M Y') }}
                        </div>
                    </div>

                    <div class="shift-date-item new">
                        <div class="shift-date-label">Start Baru</div>
                        <div class="shift-date-value">
                            {{ \Carbon\Carbon::parse($item['new_start_date'])->format('d M Y') }}
                        </div>
                    </div>

                    <div class="shift-date-item">
                        <div class="shift-date-label">End Lama</div>
                        <div class="shift-date-value">
                            {{ \Carbon\Carbon::parse($item['old_end_date'])->format('d M Y') }}
                        </div>
                    </div>

                    <div class="shift-date-item new">
                        <div class="shift-date-label">End Baru</div>
                        <div class="shift-date-value">
                            {{ \Carbon\Carbon::parse($item['new_end_date'])->format('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Konfirmasi Pergeseran</span>
        </div>

        <div class="card-body">
            <div class="shift-confirm-box">
                <strong>Perhatian:</strong>
                Konfirmasi akan memperbarui tanggal mulai dan selesai seluruh jadwal
                mesin yang dipilih. Perubahan juga akan tercatat dalam histori.
                Pastikan hasil preview sudah benar sebelum melanjutkan.
            </div>

            <form
                method="POST"
                action="{{ route('schedules.shift.apply') }}"
                onsubmit="return confirm('Yakin ingin menerapkan pergeseran jadwal ini?')">

                @csrf

                <input type="hidden" name="machine_id" value="{{ $machineId }}">
                <input type="hidden" name="shift_days" value="{{ $shiftDays }}">

                <div class="form-group">
                    <label class="form-label">Alasan Pergeseran</label>

                    <input
                        type="text"
                        name="reason"
                        class="form-control"
                        placeholder="Contoh: Penyesuaian jadwal produksi"
                        maxlength="500">

                    <div class="form-hint">
                        Keterangan opsional untuk dokumentasi histori perubahan.
                    </div>
                </div>

                <div class="shift-confirm-actions">
                    <button type="submit" class="btn btn-primary">
                        Terapkan Pergeseran
                    </button>

                    <a href="{{ route('schedules.shift.preview') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    @elseif($preview !== null && count($preview) === 0)

    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <h3>Tidak ada jadwal ditemukan</h3>
            <p>Mesin yang dipilih tidak memiliki jadwal aktif untuk digeser.</p>
        </div>
    </div>

    @endif

</div>
@endsection