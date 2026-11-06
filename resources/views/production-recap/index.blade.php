@extends('layouts.app')

@section('title', 'Rekap Produksi')
@section('page-title', 'Rekap Produksi Harian')

@push('styles')
<style>
    .recap-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .recap-filter {
        display: grid;
        grid-template-columns: repeat(4, minmax(140px, 1fr)) auto;
        gap: 12px;
        align-items: end;
        padding: 16px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .recap-filter .form-group {
        margin: 0;
        min-width: 0;
    }

    .recap-filter-actions {
        display: flex;
        gap: 8px;
    }

    .recap-summary {
        display: grid;
        grid-template-columns: repeat(8, minmax(0, 1fr));
        gap: 10px;
    }

    .recap-summary-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 14px;
        min-width: 0;
    }

    .recap-summary-label {
        color: var(--text-muted);
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .recap-summary-value {
        color: var(--text);
        font-size: 19px;
        font-weight: 700;
        margin-top: 5px;
        overflow-wrap: anywhere;
    }

    .recap-summary-secondary {
        color: var(--text-muted);
        font-size: 12px;
        margin-top: 6px;
        font-variant-numeric: tabular-nums;
    }

    .recap-section {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 0;
    }

    .recap-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .recap-section-title {
        font-size: 14px;
        font-weight: 700;
    }

    .recap-table {
        min-width: 1550px;
    }

    .recap-table td {
        white-space: nowrap;
        vertical-align: middle;
    }

    .recap-table tfoot td {
        background: #F8FAFC;
        font-weight: 700;
        border-top: 1px solid var(--border);
    }

    .recap-customer {
        max-width: 180px;
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .recap-positive {
        color: #15803D;
    }

    .recap-negative {
        color: #DC2626;
    }

    .recap-mobile-list {
        display: none;
    }

    .recap-mobile-card {
        padding: 14px;
        border-bottom: 1px solid var(--border);
    }

    .recap-mobile-card:last-child {
        border-bottom: none;
    }

    .recap-mobile-top {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 5px;
    }

    .recap-mobile-order {
        font-weight: 700;
        font-size: 13px;
    }

    .recap-mobile-customer {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 10px;
    }

    .recap-mobile-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        background: #F8FAFC;
        padding: 12px;
        border-radius: 6px;
    }

    .recap-stat-label {
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 3px;
    }

    .recap-stat-value {
        font-weight: 600;
        font-size: 12px;
        overflow-wrap: anywhere;
        font-variant-numeric: tabular-nums;
    }

    .recap-empty {
        padding: 25px;
        text-align: center;
        color: var(--text-muted);
        font-size: 12px;
    }

    @media (max-width: 1400px) {
        .recap-summary {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 1200px) {
        .recap-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .recap-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .recap-table-container {
            display: none;
        }

        .recap-mobile-list {
            display: block;
        }

        .recap-filter {
            grid-template-columns: 1fr;
        }

        .recap-filter-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
@php
$date = $recap['date'];

$autoSummary = $recap['auto_summary'];
$semiSummary = $recap['semi_summary'];

$summary = [
'demand_pcs' =>
$autoSummary['demand_pcs'] +
$semiSummary['demand_pcs'],

'demand_ton' =>
$autoSummary['demand_ton'] +
$semiSummary['demand_ton'],

'daily_output_pcs' =>
$autoSummary['daily_output_pcs'] +
$semiSummary['daily_output_pcs'],

'daily_output_ton' =>
$autoSummary['daily_output_ton'] +
$semiSummary['daily_output_ton'],

'cumulative_output_pcs' =>
$autoSummary['cumulative_output_pcs'] +
$semiSummary['cumulative_output_pcs'],

'cumulative_output_ton' =>
$autoSummary['cumulative_output_ton'] +
$semiSummary['cumulative_output_ton'],

'remaining_pcs' =>
$autoSummary['remaining_pcs'] +
$semiSummary['remaining_pcs'],

'remaining_ton' =>
$autoSummary['remaining_ton'] +
$semiSummary['remaining_ton'],
];
@endphp

<div class="recap-page">

    <form
        method="GET"
        action="{{ route('recap.index') }}"
        class="recap-filter">

        <div class="form-group">
            <label class="form-label">Tanggal Laporan</label>
            <input
                type="date"
                name="date"
                value="{{ $date }}"
                class="form-control"
                required>
        </div>

        <div class="form-group">
            <label class="form-label">Jenis Mesin</label>
            <select name="machine_type" class="form-control">
                <option value="">AUTO dan SEMI</option>
                <option
                    value="AUTO"
                    {{ ($filters['machine_type'] ?? '') === 'AUTO' ? 'selected' : '' }}>
                    AUTO
                </option>
                <option
                    value="SEMI"
                    {{ ($filters['machine_type'] ?? '') === 'SEMI' ? 'selected' : '' }}>
                    SEMI
                </option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Mesin</label>
            <select name="machine_id" class="form-control">
                <option value="">Semua Mesin</option>

                @foreach($machines as $machine)
                <option
                    value="{{ $machine->id }}"
                    {{ ($filters['machine_id'] ?? '') == $machine->id ? 'selected' : '' }}>
                    {{ $machine->machine_code }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Cari Order / Customer</label>
            <input
                type="text"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                class="form-control"
                placeholder="Nomor order atau customer">
        </div>

        <div class="recap-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">
                Tampilkan
            </button>

            <a
                href="{{ route('recap.index') }}"
                class="btn btn-secondary btn-sm">
                Reset
            </a>
        </div>
    </form>

    <section class="recap-summary">

        <div class="recap-summary-card">
            <div class="recap-summary-label">Permintaan Total</div>
            <div class="recap-summary-value">
                {{ number_format($summary['demand_pcs'], 0, ',', '.') }}
            </div>
            <div class="text-muted" style="font-size:10px;">PCS</div>
            <div class="recap-summary-secondary">
                {{ number_format($summary['demand_ton'], 2, ',', '.') }} TON
            </div>
        </div>

        <div class="recap-summary-card">
            <div class="recap-summary-label">Output Harian</div>
            <div class="recap-summary-value">
                {{ number_format($summary['daily_output_pcs'], 0, ',', '.') }}
            </div>
            <div class="text-muted" style="font-size:10px;">PCS</div>
            <div class="recap-summary-secondary">
                {{ number_format($summary['daily_output_ton'], 2, ',', '.') }} TON
            </div>
        </div>

        <div class="recap-summary-card">
            <div class="recap-summary-label">Kumulatif Produksi</div>
            <div class="recap-summary-value">
                {{ number_format($summary['cumulative_output_pcs'], 0, ',', '.') }}
            </div>
            <div class="text-muted" style="font-size:10px;">PCS</div>
            <div class="recap-summary-secondary">
                {{ number_format($summary['cumulative_output_ton'], 2, ',', '.') }} TON
            </div>
        </div>

        <div class="recap-summary-card">
            <div class="recap-summary-label">Sisa Produksi</div>
            <div class="recap-summary-value {{ $summary['remaining_pcs'] < 0 ? 'recap-negative' : '' }}">
                {{ number_format($summary['remaining_pcs'], 0, ',', '.') }}
            </div>
            <div class="text-muted" style="font-size:10px;">PCS</div>
            <div class="recap-summary-secondary {{ $summary['remaining_ton'] < 0 ? 'recap-negative' : '' }}">
                {{ number_format($summary['remaining_ton'], 2, ',', '.') }} TON
            </div>
        </div>

        <div class="recap-summary-card">
            <div class="recap-summary-label">Waste AUTO</div>
            <div class="recap-summary-value">
                {{ number_format($autoSummary['waste_kg'], 2, ',', '.') }}
            </div>
            <div class="text-muted" style="font-size:10px;">KG</div>
        </div>

        <div class="recap-summary-card">
            <div class="recap-summary-label">Waste AUTO %</div>
            <div class="recap-summary-value {{ $autoSummary['waste_pct'] > 5 ? 'recap-negative' : '' }}">
                {{ number_format($autoSummary['waste_pct'], 2, ',', '.') }}%
            </div>
            <div class="text-muted" style="font-size:10px;">PERSENTASE</div>
        </div>

        <div class="recap-summary-card">
            <div class="recap-summary-label">Waste SEMI</div>
            <div class="recap-summary-value">
                {{ number_format($semiSummary['waste_kg'], 2, ',', '.') }}
            </div>
            <div class="text-muted" style="font-size:10px;">KG</div>
        </div>

        <div class="recap-summary-card">
            <div class="recap-summary-label">Waste SEMI %</div>
            <div class="recap-summary-value {{ $semiSummary['waste_pct'] > 5 ? 'recap-negative' : '' }}">
                {{ number_format($semiSummary['waste_pct'], 2, ',', '.') }}%
            </div>
            <div class="text-muted" style="font-size:10px;">PERSENTASE</div>
        </div>

    </section>

    @foreach([
    'AUTO' => $recap['auto_rows'],
    'SEMI' => $recap['semi_rows']
    ] as $type => $rows)

    @if(
    empty($filters['machine_type'])
    || $filters['machine_type'] === $type
    )

    @php
    $typeSummary = $type === 'AUTO'
    ? $autoSummary
    : $semiSummary;
    @endphp

    <section class="recap-section">

        <div class="recap-section-heading">
            <div class="d-flex align-center gap-8">
                <span class="recap-section-title">
                    Rekap Produksi {{ $type }}
                </span>

                <span class="badge {{ $type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                    {{ count($rows) }} Order
                </span>
            </div>

            <span class="text-muted" style="font-size:11px;">
                {{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}
            </span>
        </div>

        <div class="card">

            @if(count($rows) > 0)

            <div class="table-container recap-table-container">
                <table class="recap-table">
                    <thead>
                        <tr>
                            <th>Mesin</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th class="text-right">Permintaan PCS</th>
                            <th class="text-right">Permintaan Ton</th>
                            <th class="text-right">Target/Shift</th>
                            <th class="text-right">Output Harian PCS</th>
                            <th class="text-right">Output Harian Ton</th>
                            <th class="text-right">Kumulatif PCS</th>
                            <th class="text-right">Kumulatif Ton</th>
                            <th class="text-right">Sisa PCS</th>
                            <th class="text-right">Sisa Ton</th>
                            <th class="text-right">Waste KG</th>
                            <th class="text-right">Waste %</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td>
                                <span class="badge {{ $type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                                    {{ $row['machine_code'] }}
                                </span>
                            </td>

                            <td class="font-semibold">
                                {{ $row['order_no'] }}
                            </td>

                            <td
                                class="recap-customer"
                                title="{{ $row['customer'] ?? '' }}">
                                {{ $row['customer'] ?: '-' }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($row['demand_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($row['demand_ton'], 3, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($row['capacity_per_shift'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right font-semibold">
                                {{ number_format($row['daily_output_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($row['output_ton'], 3, ',', '.') }}
                            </td>

                            <td class="num text-right font-semibold">
                                {{ number_format($row['cumulative_output_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($row['cumulative_ton'], 3, ',', '.') }}
                            </td>

                            <td class="num text-right {{ $row['remaining_pcs'] < 0 ? 'recap-negative' : '' }}">
                                {{ number_format($row['remaining_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right {{ $row['remaining_ton'] < 0 ? 'recap-negative' : '' }}">
                                {{ number_format($row['remaining_ton'], 3, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($row['waste_kg'], 2, ',', '.') }}
                            </td>

                            <td class="num text-right {{ $row['waste_pct'] > 5 ? 'recap-negative' : '' }}">
                                {{ number_format($row['waste_pct'], 2, ',', '.') }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3">
                                TOTAL {{ $type }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['demand_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['demand_ton'], 2, ',', '.') }}
                            </td>

                            <td></td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['daily_output_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['daily_output_ton'], 2, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['cumulative_output_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['cumulative_output_ton'], 2, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['remaining_pcs'], 0, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['remaining_ton'], 2, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['waste_kg'], 2, ',', '.') }}
                            </td>

                            <td class="num text-right">
                                {{ number_format($typeSummary['waste_pct'], 2, ',', '.') }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="recap-mobile-list">
                @foreach($rows as $row)
                <div class="recap-mobile-card">

                    <div class="recap-mobile-top">
                        <div class="recap-mobile-order">
                            {{ $row['order_no'] }}
                        </div>

                        <span class="badge {{ $type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                            {{ $row['machine_code'] }}
                        </span>
                    </div>

                    <div class="recap-mobile-customer">
                        {{ $row['customer'] ?: 'Customer tidak tersedia' }}
                    </div>

                    <div class="recap-mobile-stats">

                        <div>
                            <div class="recap-stat-label">Permintaan PCS</div>
                            <div class="recap-stat-value">
                                {{ number_format($row['demand_pcs'], 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Permintaan Ton</div>
                            <div class="recap-stat-value">
                                {{ number_format((float) ($row['demand_ton'] ?? 0), 3, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Target/Shift</div>
                            <div class="recap-stat-value">
                                {{ number_format($row['capacity_per_shift'], 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Output Harian PCS</div>
                            <div class="recap-stat-value">
                                {{ number_format($row['daily_output_pcs'], 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Output Harian Ton</div>
                            <div class="recap-stat-value">
                                {{ number_format((float) ($row['output_ton'] ?? 0), 3, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Kumulatif PCS</div>
                            <div class="recap-stat-value">
                                {{ number_format($row['cumulative_output_pcs'], 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Kumulatif Ton</div>
                            <div class="recap-stat-value">
                                {{ number_format((float) ($row['cumulative_ton'] ?? 0), 3, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Sisa PCS</div>
                            <div class="recap-stat-value {{ $row['remaining_pcs'] < 0 ? 'recap-negative' : '' }}">
                                {{ number_format($row['remaining_pcs'], 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Sisa Ton</div>
                            <div class="recap-stat-value {{ $row['remaining_ton'] < 0 ? 'recap-negative' : '' }}">
                                {{ number_format((float) ($row['remaining_ton'] ?? 0), 3, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Waste KG</div>
                            <div class="recap-stat-value">
                                {{ number_format($row['waste_kg'], 2, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="recap-stat-label">Waste %</div>
                            <div class="recap-stat-value {{ $row['waste_pct'] > 5 ? 'recap-negative' : '' }}">
                                {{ number_format($row['waste_pct'], 2, ',', '.') }}%
                            </div>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>

            @else
            <div class="recap-empty">
                Tidak ada data produksi {{ $type }} untuk tanggal
                {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
            </div>
            @endif

        </div>
    </section>

    @endif
    @endforeach

</div>
@endsection