@extends('layouts.app')

@section('title', 'Dashboard - Machine Production Planner')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .dashboard-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .dashboard-filter {
        display: flex;
        align-items: end;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 16px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .dashboard-filter .form-group {
        margin: 0;
        min-width: 160px;
    }

    .dashboard-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dashboard-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-left: auto;
    }

    .dashboard-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .dashboard-kpi {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 15px 16px;
        min-width: 0;
    }

    .dashboard-kpi.green {
        border-left: 3px solid var(--green);
    }

    .dashboard-kpi.yellow {
        border-left: 3px solid var(--warning);
    }

    .dashboard-kpi.red {
        border-left: 3px solid var(--danger);
    }

    .dashboard-kpi-label {
        font-size: 11px;
        font-weight: 500;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .dashboard-kpi-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text);
        margin-top: 5px;
        font-variant-numeric: tabular-nums;
        overflow-wrap: anywhere;
    }

    .dashboard-kpi-description {
        color: var(--text-muted);
        font-size: 11px;
        margin-top: 3px;
        line-height: 1.5;
    }

    .dashboard-section-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        min-width: 0;
    }

    .dashboard-section {
        min-width: 0;
    }

    .dashboard-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dashboard-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
    }

    .dashboard-chart-container {
        position: relative;
        width: 100%;
        min-height: 190px;
    }

    .dashboard-chart {
        display: block;
        width: 100%;
        height: 190px;
    }

    .dashboard-chart-unit {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 8px;
    }

    .dashboard-table {
        min-width: 450px;
    }

    .dashboard-mobile-list {
        display: none;
    }

    .dashboard-mobile-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
    }

    .dashboard-mobile-item:last-child {
        border-bottom: none;
    }

    .dashboard-mobile-main {
        min-width: 0;
    }

    .dashboard-mobile-machine {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .dashboard-mobile-order {
        font-size: 12px;
        margin-top: 3px;
        overflow-wrap: anywhere;
    }

    .dashboard-mobile-description {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
        overflow-wrap: anywhere;
    }

    .dashboard-mobile-date {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .dashboard-empty {
        padding: 26px 16px;
        text-align: center;
        color: var(--text-muted);
        font-size: 12px;
    }

    .dashboard-stop-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 7px;
    }

    @media (max-width: 1000px) {
        .dashboard-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .dashboard-filter {
            align-items: stretch;
        }

        .dashboard-filter .form-group {
            flex: 1;
            min-width: 100%;
        }

        .dashboard-filter-actions {
            width: 100%;
        }

        .dashboard-filter-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .dashboard-meta {
            width: 100%;
            margin-left: 0;
        }

        .dashboard-section-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-table-container {
            display: none;
        }

        .dashboard-mobile-list {
            display: block;
        }

        .dashboard-kpi-value {
            font-size: 22px;
        }
    }

    @media (max-width: 420px) {
        .dashboard-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .dashboard-kpi {
            padding: 12px;
        }

        .dashboard-kpi-label {
            font-size: 10px;
        }

        .dashboard-kpi-value {
            font-size: 19px;
        }

        .dashboard-kpi-description {
            font-size: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-page">

    <form method="GET" action="{{ route('dashboard') }}" class="dashboard-filter">
        <div class="form-group">
            <label class="form-label">Periode Mulai</label>
            <input
                type="date"
                name="start_date"
                value="{{ $startDate }}"
                class="form-control"
                required>
        </div>

        <div class="form-group">
            <label class="form-label">Periode Akhir</label>
            <input
                type="date"
                name="end_date"
                value="{{ $endDate }}"
                class="form-control"
                required>
        </div>

        <div class="dashboard-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">
                Filter
            </button>

            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
                Reset
            </a>
        </div>

        @if($summary['latest_mes_date'])
        <div class="dashboard-meta">
            <span class="badge badge-green">
                MES terakhir:
                {{ \Carbon\Carbon::parse($summary['latest_mes_date'])->format('d M Y') }}
            </span>
        </div>
        @endif
    </form>

    <section class="dashboard-kpi-grid">

        <div class="dashboard-kpi green">
            <div class="dashboard-kpi-label">Order Terjadwal</div>
            <div class="dashboard-kpi-value num">
                {{ number_format($summary['total_scheduled']) }}
            </div>
            <div class="dashboard-kpi-description">
                Dalam periode yang dipilih
            </div>
        </div>

        <div class="dashboard-kpi yellow">
            <div class="dashboard-kpi-label">Total MP/Shift</div>
            <div class="dashboard-kpi-value num">
                {{ number_format($summary['total_mp_shift'], 0) }}
            </div>
            <div class="dashboard-kpi-description">
                Tenaga OS 2 Shift: {{ number_format($summary['two_shift_os'], 0) }}
            </div>
        </div>

        <div class="dashboard-kpi green">
            <div class="dashboard-kpi-label">Mesin Aktif</div>
            <div class="dashboard-kpi-value num">
                {{ number_format($summary['active_machines_count']) }}
            </div>
            <div class="dashboard-kpi-description">
                Mesin jalan
            </div>
        </div>

        <div class="dashboard-kpi green">
            <div class="dashboard-kpi-label">HASIL Aktual AUTO</div>
            <div class="dashboard-kpi-value num">
                {{ number_format($summary['auto_actual_ton'], 2) }}
            </div>
            <div class="dashboard-kpi-description">
                TON
            </div>
        </div>

        <div class="dashboard-kpi green">
            <div class="dashboard-kpi-label">HASIL Aktual SEMI</div>
            <div class="dashboard-kpi-value num">
                {{ number_format($summary['semi_actual_pcs'], 0) }}
            </div>
            <div class="dashboard-kpi-description">
                PCS
            </div>
        </div>

        <div class="dashboard-kpi {{ count($summary['stop_machines']) > 0 ? 'red' : 'green' }}">
            <div class="dashboard-kpi-label">Mesin STOP Hari Ini</div>
            <div class="dashboard-kpi-value num">
                {{ number_format(count($summary['stop_machines'])) }}
            </div>

            @if(count($summary['stop_machines']) > 0)
            <div class="dashboard-stop-list">
                @foreach($summary['stop_machines'] as $machine)
                <span class="badge badge-red">
                    {{ $machine->machine_code }}
                </span>
                @endforeach
            </div>
            @else
            <div class="dashboard-kpi-description">
                Tidak ada mesin STOP
            </div>
            @endif
        </div>

    </section>


    @php
    $trendRows = collect($summary['trend'] ?? [])->values();

    $chartLeft = 55;
    $chartRight = 710;
    $chartTop = 14;
    $chartBottom = 158;

    $chartWidth = $chartRight - $chartLeft;
    $chartHeight = $chartBottom - $chartTop;

    $slotWidth = $chartWidth / max($trendRows->count(), 1);
    $barWidth = max(3, min(28, $slotWidth * 0.62));

    $autoMax = max((float) $trendRows->max('auto_ton'), 1);
    $semiMax = max((float) $trendRows->max('semi_pcs'), 1);

    $labelStep = max(1, (int) ceil($trendRows->count() / 8));
    @endphp

    @if($trendRows->isNotEmpty())
    <section class="dashboard-section-grid">

        {{-- GRAFIK AUTO --}}
        <div class="card">
            <div class="card-header">
                <div class="dashboard-section-header" style="width:100%;">
                    <span class="dashboard-section-title">Tren Aktual AUTO</span>
                    <span class="badge badge-green">TON/Hari</span>
                </div>
            </div>

            <div class="card-body">
                <div class="dashboard-chart-container">
                    <svg
                        viewBox="0 0 720 195"
                        width="100%"
                        height="190"
                        role="img"
                        aria-label="Grafik tren aktual produksi AUTO">

                        @for($i = 0; $i
                        <= 4; $i++)
                            @php
                            $ratio=$i / 4;
                            $y=$chartBottom - ($ratio * $chartHeight);
                            $tickValue=$autoMax * $ratio;
                            @endphp

                            <line
                            x1="{{ $chartLeft }}"
                            y1="{{ $y }}"
                            x2="{{ $chartRight }}"
                            y2="{{ $y }}"
                            stroke="#E2E8F0"
                            stroke-width="1" />

                        <text
                            x="48"
                            y="{{ $y + 3 }}"
                            font-size="10"
                            fill="#64748B"
                            text-anchor="end">
                            {{ number_format($tickValue, 1, ',', '.') }}
                        </text>
                        @endfor

                        @foreach($trendRows as $index => $item)
                        @php
                        $value = max((float) ($item->auto_ton ?? 0), 0);
                        $barHeight = ($value / $autoMax) * $chartHeight;

                        $x = $chartLeft
                        + ($index * $slotWidth)
                        + (($slotWidth - $barWidth) / 2);

                        $y = $chartBottom - $barHeight;

                        $dateLabel = substr((string) $item->date_str, -2);
                        @endphp

                        @if($value > 0)
                        <rect
                            x="{{ $x }}"
                            y="{{ $y }}"
                            width="{{ $barWidth }}"
                            height="{{ max($barHeight, 1) }}"
                            rx="3"
                            fill="#16A34A" />
                        @endif

                        @if($index % $labelStep === 0 || $index === $trendRows->count() - 1)
                        <text
                            x="{{ $chartLeft + ($index * $slotWidth) + ($slotWidth / 2) }}"
                            y="180"
                            font-size="10"
                            fill="#64748B"
                            text-anchor="middle">
                            {{ $dateLabel }}
                        </text>
                        @endif
                        @endforeach
                    </svg>
                </div>

                <div class="dashboard-chart-unit">
                    Total output produksi AUTO per hari.
                </div>
            </div>
        </div>

        {{-- GRAFIK SEMI --}}
        <div class="card">
            <div class="card-header">
                <div class="dashboard-section-header" style="width:100%;">
                    <span class="dashboard-section-title">Tren Aktual SEMI</span>
                    <span class="badge badge-green">PCS/Hari</span>
                </div>
            </div>

            <div class="card-body">
                <div class="dashboard-chart-container">
                    <svg
                        viewBox="0 0 720 195"
                        width="100%"
                        height="190"
                        role="img"
                        aria-label="Grafik tren aktual produksi SEMI">

                        @for($i = 0; $i
                        <= 4; $i++)
                            @php
                            $ratio=$i / 4;
                            $y=$chartBottom - ($ratio * $chartHeight);
                            $tickValue=$semiMax * $ratio;
                            @endphp

                            <line
                            x1="{{ $chartLeft }}"
                            y1="{{ $y }}"
                            x2="{{ $chartRight }}"
                            y2="{{ $y }}"
                            stroke="#E2E8F0"
                            stroke-width="1" />

                        <text
                            x="48"
                            y="{{ $y + 3 }}"
                            font-size="10"
                            fill="#64748B"
                            text-anchor="end">
                            {{ number_format($tickValue, 0, ',', '.') }}
                        </text>
                        @endfor

                        @foreach($trendRows as $index => $item)
                        @php
                        $value = max((float) ($item->semi_pcs ?? 0), 0);
                        $barHeight = ($value / $semiMax) * $chartHeight;

                        $x = $chartLeft
                        + ($index * $slotWidth)
                        + (($slotWidth - $barWidth) / 2);

                        $y = $chartBottom - $barHeight;

                        $dateLabel = substr((string) $item->date_str, -2);
                        @endphp

                        @if($value > 0)
                        <rect
                            x="{{ $x }}"
                            y="{{ $y }}"
                            width="{{ $barWidth }}"
                            height="{{ max($barHeight, 1) }}"
                            rx="3"
                            fill="#16A34A" />
                        @endif

                        @if($index % $labelStep === 0 || $index === $trendRows->count() - 1)
                        <text
                            x="{{ $chartLeft + ($index * $slotWidth) + ($slotWidth / 2) }}"
                            y="180"
                            font-size="10"
                            fill="#64748B"
                            text-anchor="middle">
                            {{ $dateLabel }}
                        </text>
                        @endif
                        @endforeach
                    </svg>
                </div>

                <div class="dashboard-chart-unit">
                    Total output produksi SEMI per hari.
                </div>
            </div>
        </div>

    </section>
    @else
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3>Belum ada data aktual produksi</h3>
            <p>Grafik akan ditampilkan setelah tersedia data MES pada periode yang dipilih.</p>
        </div>
    </div>
    @endif


    <section class="dashboard-section-grid">

        <div class="card">
            <div class="card-header">
                <div class="dashboard-section-header" style="width:100%;">
                    <span class="dashboard-section-title">Jadwal Berjalan</span>
                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
                        Lihat Semua
                    </a>
                </div>
            </div>

            @if($summary['running_schedules']->count() > 0)

            <div class="table-container dashboard-table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Mesin</th>
                            <th>Order</th>
                            <th>MP/Shift</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($summary['running_schedules'] as $schedule)
                        <tr>
                            <td>
                                <span class="badge badge-green">
                                    {{ $schedule->machine_code }}
                                </span>
                            </td>

                            <td>
                                <div class="font-semibold">
                                    {{ $schedule->order_no }}
                                </div>

                                <div class="text-muted" style="font-size:11px;">
                                    {{ $schedule->note ?? '-' }}
                                </div>
                            </td>

                            <td class="num text-right">
                                {{ $schedule->mp_ideal_per_shift !== null ? number_format($schedule->mp_ideal_per_shift, 0) : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="dashboard-mobile-list">
                @foreach($summary['running_schedules'] as $schedule)
                <div class="dashboard-mobile-item">
                    <div class="dashboard-mobile-main">
                        <div class="dashboard-mobile-machine">
                            {{ $schedule->machine_code }}
                        </div>

                        <div class="dashboard-mobile-order">
                            {{ $schedule->order_no }}
                        </div>

                        <div class="dashboard-mobile-description">
                            {{ $schedule->note ?? '-' }}
                        </div>
                    </div>

                    <div class="dashboard-mobile-date">
                        {{ $schedule->mp_ideal_per_shift !== null ? number_format($schedule->mp_ideal_per_shift, 1) . ' MP' : '-' }}
                    </div>
                </div>
                @endforeach
            </div>

            @else
            <div class="dashboard-empty">
                Tidak ada jadwal produksi yang berjalan hari ini.
            </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <span class="dashboard-section-title">Order Berikutnya</span>
            </div>

            @if($summary['next_schedules']->count() > 0)

            <div class="table-container dashboard-table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Mesin</th>
                            <th>Order</th>
                            <th>Mulai</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($summary['next_schedules'] as $schedule)
                        <tr>
                            <td>
                                <span class="badge badge-green">
                                    {{ $schedule->machine_code }}
                                </span>
                            </td>

                            <td class="font-semibold">
                                {{ $schedule->order_no }}
                            </td>

                            <td class="nowrap text-muted">
                                {{ \Carbon\Carbon::parse($schedule->start_date)->format('d M Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="dashboard-mobile-list">
                @foreach($summary['next_schedules'] as $schedule)
                <div class="dashboard-mobile-item">
                    <div class="dashboard-mobile-main">
                        <div class="dashboard-mobile-machine">
                            {{ $schedule->machine_code }}
                        </div>

                        <div class="dashboard-mobile-order">
                            {{ $schedule->order_no }}
                        </div>
                    </div>

                    <div class="dashboard-mobile-date">
                        {{ \Carbon\Carbon::parse($schedule->start_date)->format('d M Y') }}
                    </div>
                </div>
                @endforeach
            </div>

            @else
            <div class="dashboard-empty">
                Tidak ada order berikutnya.
            </div>
            @endif
        </div>

    </section>

</div>
@endsection