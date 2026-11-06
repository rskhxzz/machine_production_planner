@extends('layouts.app')

@section('title', 'Preview Import Jadwal')
@section('page-title', 'Preview Import Jadwal Produksi')

@push('styles')
<style>
    .schedule-preview-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .schedule-preview-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .schedule-preview-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .schedule-preview-summary-item {
        padding: 14px 16px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
        min-width: 0;
    }

    .schedule-preview-summary-item.green {
        border-left: 3px solid var(--green);
    }

    .schedule-preview-summary-item.yellow {
        border-left: 3px solid var(--warning);
    }

    .schedule-preview-summary-item.red {
        border-left: 3px solid var(--danger);
    }

    .schedule-preview-summary-label {
        font-size: 11px;
        color: var(--text-muted);
    }

    .schedule-preview-summary-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--text);
        margin-top: 3px;
        font-variant-numeric: tabular-nums;
    }

    .schedule-preview-table {
        min-width: 850px;
    }

    .schedule-preview-table td {
        white-space: nowrap;
    }

    .schedule-preview-mobile {
        display: none;
    }

    .schedule-preview-mobile-card {
        padding: 14px;
        border-bottom: 1px solid var(--border);
    }

    .schedule-preview-mobile-card:last-child {
        border-bottom: none;
    }

    .schedule-preview-mobile-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
    }

    .schedule-preview-mobile-order {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        overflow-wrap: anywhere;
    }

    .schedule-preview-mobile-machine {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
    }

    .schedule-preview-mobile-info {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 11px;
        background: #F8FAFC;
        border-radius: 6px;
    }

    .schedule-preview-info-label {
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 3px;
    }

    .schedule-preview-info-value {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        overflow-wrap: anywhere;
    }

    .schedule-preview-error {
        margin-top: 10px;
        padding: 9px 10px;
        border-radius: 6px;
        background: #FEF2F2;
        color: #B91C1C;
        font-size: 11px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .schedule-preview-errors {
        padding: 14px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-radius: 7px;
        color: #92400E;
    }

    .schedule-preview-error-list {
        margin-top: 8px;
        padding-left: 20px;
        max-height: 160px;
        overflow-y: auto;
        font-size: 12px;
        line-height: 1.7;
    }

    .schedule-preview-error-list li {
        overflow-wrap: anywhere;
    }

    .schedule-preview-confirm-info {
        padding: 12px 14px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-radius: 7px;
        color: #92400E;
        font-size: 12px;
        line-height: 1.7;
        margin-bottom: 14px;
    }

    .schedule-preview-confirm-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .schedule-preview-pagination-note {
        padding: 10px 14px;
        border-top: 1px solid var(--border);
        color: var(--text-muted);
        font-size: 11px;
    }

    @media (max-width: 900px) {
        .schedule-preview-table-container {
            display: none;
        }

        .schedule-preview-mobile {
            display: block;
        }
    }

    @media (max-width: 600px) {
        .schedule-preview-summary {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .schedule-preview-summary-item {
            padding: 11px 13px;
        }

        .schedule-preview-summary-value {
            font-size: 19px;
        }

        .schedule-preview-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .schedule-preview-toolbar .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 420px) {
        .schedule-preview-confirm-actions {
            flex-direction: column;
        }

        .schedule-preview-confirm-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
@php
$totalRecords = count($records);
$totalErrors = is_countable($errors) ? count($errors) : 0;
$validRecords = 0;

foreach ($records as $record) {
if (!isset($record['_error'])) {
$validRecords++;
}
}
@endphp

<div class="schedule-preview-page">

    <div class="schedule-preview-toolbar">
        <a href="{{ route('schedules.import') }}" class="btn btn-secondary btn-sm">
            Kembali ke Upload
        </a>

        <div class="d-flex gap-8" style="flex-wrap:wrap;">
            <span class="badge badge-green">
                {{ number_format($validRecords) }} baris valid
            </span>

            @if($totalErrors > 0)
            <span class="badge badge-red">
                {{ number_format($totalErrors) }} kesalahan
            </span>
            @endif
        </div>
    </div>

    <div class="schedule-preview-summary">
        <div class="schedule-preview-summary-item">
            <div class="schedule-preview-summary-label">Total Baris</div>
            <div class="schedule-preview-summary-value">
                {{ number_format($totalRecords) }}
            </div>
        </div>

        <div class="schedule-preview-summary-item green">
            <div class="schedule-preview-summary-label">Baris Valid</div>
            <div class="schedule-preview-summary-value">
                {{ number_format($validRecords) }}
            </div>
        </div>

        <div class="schedule-preview-summary-item {{ $totalErrors > 0 ? 'red' : 'yellow' }}">
            <div class="schedule-preview-summary-label">Baris Bermasalah</div>
            <div class="schedule-preview-summary-value">
                {{ number_format($totalErrors) }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Preview Jadwal Produksi</span>

            <span class="text-muted" style="font-size:11px;">
                Maksimal 50 baris ditampilkan
            </span>
        </div>

        @if($totalRecords > 0)

        <div class="table-container schedule-preview-table-container">
            <table class="schedule-preview-table">
                <thead>
                    <tr>
                        <th>Baris</th>
                        <th>Mesin</th>
                        <th>Sequence</th>
                        <th>Order</th>
                        <th>Note</th>
                        <th>Start</th>
                        <th>End</th>
                        <th class="text-right">MP/Shift</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(array_slice($records, 0, 50) as $record)
                    <tr>
                        <td class="num text-muted">
                            {{ $record['_row'] ?? '-' }}
                        </td>

                        <td>
                            <span class="badge {{ isset($record['machine_id']) ? 'badge-green' : 'badge-red' }}">
                                {{ $record['machine_code'] ?? '-' }}
                            </span>
                        </td>

                        <td class="num">
                            {{ $record['schedule_sequence'] ?? '-' }}
                        </td>

                        <td class="font-semibold">
                            {{ $record['order_no'] ?? '-' }}
                        </td>

                        <td class="text-muted">
                            {{ $record['note'] ?? '-' }}
                        </td>

                        <td>
                            {{ $record['start_date'] ?? '-' }}
                        </td>

                        <td>
                            {{ $record['end_date'] ?? '-' }}
                        </td>

                        <td class="num text-right">
                            {{ $record['mp_ideal_per_shift'] ?? '-' }}
                        </td>

                        <td>
                            @if(isset($record['_error']))
                            <span class="badge badge-red" title="{{ $record['_error'] }}">
                                Error
                            </span>
                            @else
                            <span class="badge badge-green">Valid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="schedule-preview-mobile">
            @foreach(array_slice($records, 0, 50) as $record)
            <div class="schedule-preview-mobile-card">
                <div class="schedule-preview-mobile-top">
                    <div style="min-width:0;">
                        <div class="schedule-preview-mobile-order">
                            {{ $record['order_no'] ?? '-' }}
                        </div>

                        <div class="schedule-preview-mobile-machine">
                            Baris {{ $record['_row'] ?? '-' }}
                            · Mesin {{ $record['machine_code'] ?? '-' }}
                        </div>
                    </div>

                    @if(isset($record['_error']))
                    <span class="badge badge-red">Error</span>
                    @else
                    <span class="badge badge-green">Valid</span>
                    @endif
                </div>

                <div class="schedule-preview-mobile-info">
                    <div>
                        <div class="schedule-preview-info-label">Sequence</div>
                        <div class="schedule-preview-info-value">
                            {{ $record['schedule_sequence'] ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="schedule-preview-info-label">MP/Shift</div>
                        <div class="schedule-preview-info-value">
                            {{ $record['mp_ideal_per_shift'] ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="schedule-preview-info-label">Start Date</div>
                        <div class="schedule-preview-info-value">
                            {{ $record['start_date'] ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="schedule-preview-info-label">End Date</div>
                        <div class="schedule-preview-info-value">
                            {{ $record['end_date'] ?? '-' }}
                        </div>
                    </div>
                </div>

                @if(!empty($record['note']))
                <div style="font-size:11px;color:var(--text-muted);margin-top:8px;">
                    Note: {{ $record['note'] }}
                </div>
                @endif

                @if(isset($record['_error']))
                <div class="schedule-preview-error">
                    {{ $record['_error'] }}
                </div>
                @endif
            </div>
            @endforeach
        </div>

        @if($totalRecords > 50)
        <div class="schedule-preview-pagination-note">
            Menampilkan 50 dari {{ number_format($totalRecords) }} baris.
            Semua baris akan divalidasi saat konfirmasi import.
        </div>
        @endif

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>Tidak ada data untuk diproses</h3>
            <p>Periksa kembali format dan isi file Excel Anda.</p>
        </div>
        @endif
    </div>

    @if($totalErrors > 0)
    <div class="schedule-preview-errors">
        <strong>
            {{ number_format($totalErrors) }} baris bermasalah
        </strong>

        <p style="font-size:12px;margin-top:4px;">
            Periksa keterangan berikut untuk mengetahui baris yang perlu diperbaiki.
        </p>

        <ul class="schedule-preview-error-list">
            @foreach($errors as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($totalRecords > 0)
    <div class="card">
        <div class="card-body">
            <div class="schedule-preview-confirm-info">
                <strong>Informasi Import:</strong>
                Jadwal yang valid akan ditambahkan ke database.
                Baris yang bermasalah akan dilewati sesuai hasil validasi import.
                Data jadwal yang sudah ada tidak akan dihapus.
            </div>

            <div class="d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
                <div style="font-size:12px;color:var(--text-muted);">
                    {{ number_format($validRecords) }} baris valid siap diproses.
                </div>

                <div class="schedule-preview-confirm-actions">
                    <form method="POST" action="{{ route('schedules.import.confirm') }}">
                        @csrf

                        <button type="submit" class="btn btn-primary">
                            Konfirmasi Import
                        </button>
                    </form>

                    <a href="{{ route('schedules.import') }}" class="btn btn-secondary">
                        Upload Ulang
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection