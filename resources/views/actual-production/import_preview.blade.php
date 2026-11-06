@extends('layouts.app')

@section('title', 'Preview Import MES')
@section('page-title', 'Preview Import DATA MES')

@push('styles')
<style>
    .mes-preview-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .mes-preview-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .mes-preview-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        color: var(--text-muted);
        font-size: 12px;
    }

    .mes-preview-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .mes-preview-summary-item {
        padding: 14px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: #fff;
    }

    .mes-preview-summary-label {
        font-size: 11px;
        color: var(--text-muted);
    }

    .mes-preview-summary-value {
        font-size: 21px;
        font-weight: 700;
        color: var(--text);
        margin-top: 3px;
        font-variant-numeric: tabular-nums;
    }

    .mes-preview-summary-item.green {
        border-left: 3px solid var(--green);
    }

    .mes-preview-summary-item.yellow {
        border-left: 3px solid var(--warning);
    }

    .mes-preview-table {
        min-width: 760px;
    }

    .mes-preview-mobile {
        display: none;
    }

    .mes-preview-date-card {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
    }

    .mes-preview-date-card:last-child {
        border-bottom: none;
    }

    .mes-preview-date-name {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .mes-preview-date-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .mes-preview-stat-label {
        font-size: 10px;
        color: var(--text-muted);
    }

    .mes-preview-stat-value {
        font-size: 13px;
        font-weight: 600;
        margin-top: 2px;
        font-variant-numeric: tabular-nums;
    }

    .mes-preview-record {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
    }

    .mes-preview-record:last-child {
        border-bottom: none;
    }

    .mes-preview-record-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
    }

    .mes-preview-order {
        font-size: 13px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .mes-preview-record-meta {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
    }

    .mes-preview-record-customer {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 10px;
        overflow-wrap: anywhere;
    }

    .mes-preview-record-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        background: #F8FAFC;
        border-radius: 6px;
        padding: 10px;
    }

    .mes-preview-errors {
        padding: 14px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-radius: 7px;
        color: #92400E;
    }

    .mes-preview-error-list {
        margin-top: 8px;
        padding-left: 20px;
        max-height: 160px;
        overflow-y: auto;
        font-size: 12px;
        line-height: 1.7;
    }

    .mes-preview-error-list li {
        overflow-wrap: anywhere;
    }

    .mes-preview-confirm {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .mes-preview-confirm-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .mes-preview-note {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .mes-preview-warning {
        padding: 12px 14px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-radius: 7px;
        color: #92400E;
        font-size: 12px;
        line-height: 1.6;
        margin-bottom: 14px;
    }

    @media (max-width: 768px) {
        .mes-preview-summary {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .mes-preview-summary-item {
            padding: 11px 13px;
        }

        .mes-preview-summary-value {
            font-size: 18px;
        }

        .mes-preview-table-container {
            display: none;
        }

        .mes-preview-mobile {
            display: block;
        }

        .mes-preview-confirm {
            align-items: stretch;
            flex-direction: column;
        }

        .mes-preview-confirm-actions {
            width: 100%;
        }

        .mes-preview-confirm-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 420px) {
        .mes-preview-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .mes-preview-toolbar .btn {
            width: 100%;
            justify-content: center;
        }

        .mes-preview-confirm-actions {
            flex-direction: column;
        }

        .mes-preview-confirm-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
$errorsCount = count($preview['errors'] ?? []);
$totalRows = (int) ($preview['total_rows'] ?? 0);
$datesSummary = $preview['dates_summary'] ?? [];
$records = $preview['records'] ?? [];
$hasErrors = $errorsCount > 0;
$canConfirm = !$hasErrors && $totalRows > 0;
$totalArchived = collect($datesSummary)->sum('existing_rows');
@endphp

<div class="mes-preview-page">

    <div class="mes-preview-toolbar">
        <a href="{{ route('mes.import') }}" class="btn btn-secondary btn-sm">
            Kembali ke Upload
        </a>

        <div class="mes-preview-info">
            <span class="badge badge-blue">{{ number_format($totalRows) }} data baru</span>
            @if($hasErrors)
            <span class="badge badge-yellow">{{ number_format($errorsCount) }} kesalahan</span>
            @else
            <span class="badge badge-green">Preview valid</span>
            @endif
        </div>
    </div>

    <div class="mes-preview-summary">
        <div class="mes-preview-summary-item green">
            <div class="mes-preview-summary-label">Total Data Baru</div>
            <div class="mes-preview-summary-value">{{ number_format($totalRows) }}</div>
        </div>

        <div class="mes-preview-summary-item">
            <div class="mes-preview-summary-label">Tanggal Terdampak</div>
            <div class="mes-preview-summary-value">{{ number_format(count($datesSummary)) }}</div>
        </div>

        <div class="mes-preview-summary-item yellow">
            <div class="mes-preview-summary-label">Data Lama yang Akan Diarsip</div>
            <div class="mes-preview-summary-value">{{ number_format($totalArchived) }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Tanggal yang Terdampak</span>
            <span class="text-muted" style="font-size:11px;">
                {{ count($datesSummary) }} tanggal
            </span>
        </div>

        @if(count($datesSummary) > 0)

        <div class="table-container mes-preview-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Produksi</th>
                        <th class="text-right">Data Baru</th>
                        <th class="text-right">Data Lama</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datesSummary as $ds)
                    <tr>
                        <td class="font-semibold">
                            {{ \Carbon\Carbon::parse($ds['date'])->format('d M Y') }}
                        </td>

                        <td class="num text-right text-green">
                            {{ number_format($ds['new_rows']) }}
                        </td>

                        <td class="num text-right">
                            {{ number_format($ds['existing_rows']) }}
                        </td>

                        <td>
                            @if($ds['existing_rows'] > 0)
                            <span class="badge badge-yellow">Akan diarsip</span>
                            @else
                            <span class="badge badge-green">Data baru</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mes-preview-mobile">
            @foreach($datesSummary as $ds)
            <div class="mes-preview-date-card">
                <div class="mes-preview-date-name">
                    {{ \Carbon\Carbon::parse($ds['date'])->format('d M Y') }}
                </div>

                <div class="mes-preview-date-stats">
                    <div>
                        <div class="mes-preview-stat-label">Data Baru</div>
                        <div class="mes-preview-stat-value text-green">
                            {{ number_format($ds['new_rows']) }}
                        </div>
                    </div>

                    <div>
                        <div class="mes-preview-stat-label">Data Lama</div>
                        <div class="mes-preview-stat-value">
                            {{ number_format($ds['existing_rows']) }}
                        </div>
                    </div>
                </div>

                <div style="margin-top:8px;">
                    @if($ds['existing_rows'] > 0)
                    <span class="badge badge-yellow">Akan diarsip</span>
                    @else
                    <span class="badge badge-green">Data baru</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @else
        <div class="empty-state" style="padding:24px;">
            <p>Tidak ada tanggal produksi yang dapat diproses.</p>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Preview Data Produksi</span>
            <span class="text-muted" style="font-size:11px;">
                Menampilkan maksimal 50 baris
            </span>
        </div>

        @if(count($records) > 0)

        <div class="table-container mes-preview-table-container">
            <table class="mes-preview-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Mesin</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th class="text-right">Output KG</th>
                        <th class="text-right">Qty Prod</th>
                        <th>UOM</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($records, 0, 50) as $record)
                    @php
                    $outputKg = (float) ($record['prd_a'] ?? 0)
                    + (float) ($record['prd_b'] ?? 0);
                    @endphp

                    <tr>
                        <td class="nowrap">
                            {{ $record['datprd'] ?? '-' }}
                        </td>

                        <td class="text-center">
                            {{ $record['shft'] ?? '-' }}
                        </td>

                        <td>
                            <span class="badge badge-green">
                                {{ $record['mcnnam'] ?? '-' }}
                            </span>
                        </td>

                        <td class="font-semibold">
                            {{ $record['prpno'] ?? '-' }}
                        </td>

                        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;">
                            {{ $record['custnam'] ?? '-' }}
                        </td>

                        <td class="num text-right">
                            {{ number_format($outputKg, 2) }}
                        </td>

                        <td class="num text-right">
                            {{ ($record['qtyprd'] ?? null) !== null ? number_format((float) $record['qtyprd'], 0) : '-' }}
                        </td>

                        <td>{{ $record['uom'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mes-preview-mobile">
            @foreach(array_slice($records, 0, 50) as $record)
            @php
            $outputKg = (float) ($record['prd_a'] ?? 0)
            + (float) ($record['prd_b'] ?? 0);
            @endphp

            <div class="mes-preview-record">
                <div class="mes-preview-record-top">
                    <div style="min-width:0;">
                        <div class="mes-preview-order">
                            {{ $record['prpno'] ?? '-' }}
                        </div>

                        <div class="mes-preview-record-meta">
                            {{ $record['datprd'] ?? '-' }}
                            · Shift {{ $record['shft'] ?? '-' }}
                        </div>
                    </div>

                    <span class="badge badge-green">
                        {{ $record['mcnnam'] ?? '-' }}
                    </span>
                </div>

                <div class="mes-preview-record-customer">
                    {{ $record['custnam'] ?? '-' }}
                </div>

                <div class="mes-preview-record-stats">
                    <div>
                        <div class="mes-preview-stat-label">Output KG</div>
                        <div class="mes-preview-stat-value">
                            {{ number_format($outputKg, 2) }}
                        </div>
                    </div>

                    <div>
                        <div class="mes-preview-stat-label">Qty Produksi</div>
                        <div class="mes-preview-stat-value">
                            {{ ($record['qtyprd'] ?? null) !== null ? number_format((float) $record['qtyprd'], 0) : '-' }}
                            {{ $record['uom'] ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if(count($records) > 50)
        <div style="padding:10px 14px;border-top:1px solid var(--border);font-size:11px;color:var(--text-muted);">
            Menampilkan 50 dari {{ number_format($totalRows) }} baris. Semua data akan diproses saat konfirmasi.
        </div>
        @endif

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>Tidak ada data yang dapat diproses</h3>
            <p>Silakan periksa kembali file Excel yang diunggah.</p>
        </div>
        @endif
    </div>

    @if($hasErrors)
    <div class="mes-preview-errors">
        <strong>
            Terdapat {{ number_format($errorsCount) }} kesalahan pada file.
        </strong>

        <p style="font-size:12px;margin-top:4px;">
            Perbaiki kesalahan pada file Excel, kemudian upload ulang sebelum melakukan import.
        </p>

        <ul class="mes-preview-error-list">
            @foreach($preview['errors'] as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="mes-preview-warning">
                <strong>Perhatian:</strong>
                Data lama pada tanggal yang tercantum di atas akan diarsipkan
                (soft delete), kemudian diganti dengan data baru dari file.
                Pastikan seluruh data sudah benar sebelum melanjutkan.
            </div>

            @if(!$canConfirm)
            <div class="alert alert-warning">
                <span>⚠</span>
                <span>
                    Import belum dapat dikonfirmasi.
                    @if($hasErrors)
                    Masih terdapat kesalahan pada file.
                    @else
                    Tidak ada data valid yang dapat diproses.
                    @endif
                </span>
            </div>
            @endif

            <div class="mes-preview-confirm">
                <div class="mes-preview-note">
                    @if($canConfirm)
                    Siap mengimport {{ number_format($totalRows) }} baris data.
                    @else
                    Silakan upload ulang file setelah memperbaiki masalah.
                    @endif
                </div>

                <div class="mes-preview-confirm-actions">
                    @if($canConfirm)
                    <form method="POST" action="{{ route('mes.import.confirm') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Konfirmasi Import ({{ number_format($totalRows) }})
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('mes.import') }}" class="btn btn-secondary">
                        Upload Ulang
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection