@extends('layouts.app')

@section('title', 'Manpower')
@section('page-title', 'Manpower Harian')

@push('styles')
<style>
    .mp-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .mp-filter,
    .mp-card,
    .mp-kpi {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .mp-filter {
        display: flex;
        align-items: end;
        gap: 10px;
        flex-wrap: wrap;
        padding: 14px 16px;
    }

    .mp-filter .form-group {
        margin: 0;
        min-width: 180px;
    }

    .mp-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .mp-kpi {
        border-left: 3px solid var(--primary);
        padding: 14px 16px;
        min-width: 0;
    }

    .mp-kpi.green {
        border-left-color: var(--green);
    }

    .mp-kpi.yellow {
        border-left-color: var(--warning);
    }

    .mp-kpi-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .mp-kpi-value {
        font-size: 25px;
        font-weight: 700;
        margin-top: 5px;
        color: var(--text);
        font-variant-numeric: tabular-nums;
    }

    .mp-kpi-unit {
        font-size: 12px;
        font-weight: 500;
    }

    .mp-card {
        padding: 16px;
        min-width: 0;
    }

    .mp-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .mp-section-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
    }

    .mp-section-subtitle {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .mp-chart-legend {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 14px;
    }

    .mp-legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: var(--text-secondary);
    }

    .mp-legend-color {
        width: 10px;
        height: 10px;
        border-radius: 2px;
        flex-shrink: 0;
    }

    .mp-chart-key {
        display: flex;
        gap: 16px;
        margin-bottom: 8px;
        font-size: 11px;
        color: var(--text-muted);
    }

    .mp-chart-scroll {
        overflow-x: auto;
        padding: 10px 2px 8px;
        border-top: 1px solid var(--border);
    }

    .mp-chart-grid {
        display: flex;
        align-items: stretch;
        gap: 7px;
        min-width: max-content;
    }

    .mp-chart-day {
        width: 62px;
        min-width: 62px;
        text-align: center;
        padding: 4px 2px 0;
        border-radius: 6px;
    }

    .mp-chart-day.today {
        background: #F1F5F9;
    }

    .mp-chart-bars {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 5px;
        height: 174px;
        padding-top: 20px;
    }

    .mp-chart-column {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        width: 25px;
        height: 154px;
    }

    .mp-chart-stack {
        position: relative;
        display: flex;
        flex-direction: column-reverse;
        width: 25px;
        flex-shrink: 0;
        border-radius: 3px 3px 0 0;
        overflow: visible;
    }

    .mp-chart-total {
        position: absolute;
        top: -17px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        font-weight: 700;
        color: var(--text);
        white-space: nowrap;
    }

    .mp-chart-segment {
        width: 100%;
        min-height: 2px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        overflow: hidden;
    }

    .mp-chart-empty {
        height: 3px;
        width: 25px;
        border-top: 2px dashed #9CA3AF;
        position: relative;
    }

    .mp-chart-empty span {
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        color: var(--text-muted);
        font-size: 12px;
    }

    .mp-chart-column-label {
        font-size: 9px;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .mp-chart-date {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-top: 7px;
    }

    .mp-chart-status {
        font-size: 9px;
        color: var(--text-muted);
        margin-top: 2px;
        min-height: 12px;
    }

    .mp-breakdown-table {
        width: 100%;
        min-width: 650px;
    }

    .mp-breakdown-table td {
        vertical-align: middle;
    }

    .mp-breakdown-table tfoot td {
        background: #F8FAFC;
        font-weight: 700;
    }

    .mp-shift-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .mp-shift-summary-card {
        padding: 13px 15px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: #F8FAFC;
    }

    .mp-shift-summary-label {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .mp-shift-summary-value {
        font-size: 22px;
        font-weight: 700;
        margin-top: 5px;
        color: var(--text);
    }

    .mp-realization-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }

    .mp-summary-item {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 9px 12px;
        min-width: 125px;
    }

    .mp-summary-label {
        font-size: 10px;
        color: var(--text-muted);
    }

    .mp-summary-value {
        font-size: 17px;
        font-weight: 700;
        margin-top: 3px;
        color: var(--text);
    }

    .mp-realization-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }

    .mp-realization-grid .form-group,
    .mp-params-grid .form-group {
        margin-bottom: 0;
    }

    .mp-realization-grid .form-control {
        font-size: 15px;
        font-weight: 600;
    }

    .mp-form-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .mp-machine-table {
        min-width: 1050px;
    }

    .mp-machine-table td {
        vertical-align: middle;
    }

    .mp-machine-action {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 190px;
    }

    .mp-machine-input {
        width: 72px;
        min-width: 72px;
        padding: 6px 7px;
        font-size: 12px;
    }

    .mp-order-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 130px;
    }

    .mp-order-item {
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.4;
    }

    .mp-params-card {
        max-width: 720px;
    }

    .mp-params-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .mp-calculation-note {
        margin-top: 14px;
        padding: 11px 12px;
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 11px;
        color: var(--text-secondary);
        line-height: 1.7;
    }

    .mp-table-scroll {
        overflow-x: auto;
    }

    @media (max-width: 1100px) {
        .mp-realization-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .mp-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .mp-filter .form-group {
            width: 100%;
            min-width: 100%;
        }

        .mp-realization-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mp-params-card {
            max-width: none;
        }
    }

    @media (max-width: 420px) {
        .mp-kpi {
            padding: 12px;
        }

        .mp-kpi-value {
            font-size: 20px;
        }

        .mp-kpi-label {
            font-size: 10px;
        }

        .mp-realization-grid,
        .mp-params-grid,
        .mp-shift-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
$chartSegments = [
[
'key' => 'os',
'label' => 'OS',
'color' => '#DC2626'
],
[
'key' => 'tk',
'label' => 'TK',
'color' => '#2563EB'
],
[
'key' => 'magang',
'label' => 'Magang',
'color' => '#16A34A'
],
[
'key' => 'non_shift_assistance',
'label' => 'Non-Shift',
'color' => '#D97706'
],
[
'key' => 'additional_manpower',
'label' => 'Additional',
'color' => '#7C3AED'
],
];

$chartMax = max(
1,
(int) ($chartData['max_total'] ?? 1)
);

$plannedTotal = (int) $data['planned_total'];
$realizationTotal = $realization?->total_realization;

$realizationGap = $realizationTotal !== null
? max(0, $plannedTotal - $realizationTotal)
: null;

$realizationSurplus = $realizationTotal !== null
? max(0, $realizationTotal - $plannedTotal)
: null;
@endphp

<div
    class="mp-page"
    id="mpPage"
    data-work-date="{{ $date }}"
    data-override-url="{{ route('manpower.override.update') }}"
    data-reset-url="{{ route('manpower.override.reset') }}"
    data-params-url="{{ route('manpower.params.update') }}"
    data-realization-url="{{ route('manpower.realization.update') }}">

    <form
        method="GET"
        action="{{ route('manpower.index') }}"
        class="mp-filter"
        id="mpFilterForm">

        <div class="form-group">
            <label class="form-label" for="mpDateFilter">
                Tanggal Perhitungan
            </label>

            <input
                type="date"
                name="date"
                id="mpDateFilter"
                value="{{ $date }}"
                class="form-control">
        </div>

        <a
            href="{{ route('manpower.index') }}"
            class="btn btn-secondary btn-sm">
            Hari Ini
        </a>
    </form>

    <section class="mp-kpi-grid">

        <div class="mp-kpi green">
            <div class="mp-kpi-label">
                OS Shift 1
            </div>

            <div class="mp-kpi-value">
                {{ number_format($data['os_by_shift'][1] ?? 0, 0, ',', '.') }}
                <span class="mp-kpi-unit">Orang</span>
            </div>
        </div>

        <div class="mp-kpi green">
            <div class="mp-kpi-label">
                OS Shift 2
            </div>

            <div class="mp-kpi-value">
                {{ number_format($data['os_by_shift'][2] ?? 0, 0, ',', '.') }}
                <span class="mp-kpi-unit">Orang</span>
            </div>
        </div>

        <div class="mp-kpi yellow">
            <div class="mp-kpi-label">
                Total OS 1 Hari
            </div>

            <div class="mp-kpi-value" id="osPerDay">
                {{ number_format($data['os_per_day'], 0, ',', '.') }}
                <span class="mp-kpi-unit">Orang</span>
            </div>
        </div>

        <div class="mp-kpi">
            <div class="mp-kpi-label">
                Total MP 1 Hari
            </div>

            <div class="mp-kpi-value" id="totalMpDay">
                {{ number_format($data['planned_total'], 0, ',', '.') }}
                <span class="mp-kpi-unit">Orang</span>
            </div>
        </div>

    </section>

    <section class="mp-card">
        <div class="mp-section-heading">
            <div>
                <div class="mp-section-title">
                    Rincian Kebutuhan Manpower
                </div>

                <div class="mp-section-subtitle">
                    Kebutuhan mesin 3 shift dan pembagian OS 2 shift.
                </div>
            </div>
        </div>

        <div class="mp-table-scroll">
            <table class="mp-breakdown-table">
                <thead>
                    <tr>
                        <th>Shift Mesin</th>
                        <th class="text-right">Kebutuhan Mesin</th>
                        <th class="text-right">TK</th>
                        <th class="text-right">Magang</th>
                        <th class="text-right">Sisa Kebutuhan OS</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($data['shift_breakdown'] as $shift)
                    <tr>
                        <td class="font-semibold">
                            Shift {{ $shift['shift'] }} (8 Jam)
                        </td>

                        <td class="num text-right">
                            {{ number_format($shift['total_mp'], 0, ',', '.') }}
                        </td>

                        <td class="num text-right">
                            {{ number_format($shift['tk'], 0, ',', '.') }}
                        </td>

                        <td class="num text-right">
                            {{ number_format($shift['magang'], 0, ',', '.') }}
                        </td>

                        <td class="num text-right font-semibold">
                            {{ number_format($shift['os'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td class="font-semibold">Total Penugasan</td>

                        <td class="num text-right">
                            {{ number_format($data['machine_mp_day'], 0, ',', '.') }}
                        </td>

                        <td class="num text-right">
                            {{ number_format($data['tk_per_day'], 0, ',', '.') }}
                        </td>

                        <td class="num text-right">
                            {{ number_format($data['magang_per_day'], 0, ',', '.') }}
                        </td>

                        <td class="num text-right font-semibold">
                            {{ number_format(
                                ($data['shift_breakdown'][1]['os'] ?? 0)
                                + ($data['shift_breakdown'][2]['os'] ?? 0)
                                + ($data['shift_breakdown'][3]['os'] ?? 0),
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mp-shift-summary">
            <div class="mp-shift-summary-card">
                <div class="mp-shift-summary-label">
                    OS Shift 1 · 06.00–18.00
                </div>

                <div class="mp-shift-summary-value">
                    {{ number_format($data['os_by_shift'][1] ?? 0, 0, ',', '.') }}
                    Orang
                </div>
            </div>

            <div class="mp-shift-summary-card">
                <div class="mp-shift-summary-label">
                    OS Shift 2 · 18.00–06.00
                </div>

                <div class="mp-shift-summary-value">
                    {{ number_format($data['os_by_shift'][2] ?? 0, 0, ',', '.') }}
                    Orang
                </div>
            </div>
        </div>
    </section>

    <section class="mp-card">
        <div class="mp-section-heading">
            <div>
                <div class="mp-section-title">
                    Grafik Plan vs Selesai
                </div>

                <div class="mp-section-subtitle">
                    {{ $chartData['month_label'] }}
                    · Perbandingan manpower setiap hari
                </div>
            </div>
        </div>

        <div class="mp-chart-legend">
            @foreach($chartSegments as $segment)
            <div class="mp-legend-item">
                <span
                    class="mp-legend-color"
                    style="background: {{ $segment['color'] }};">
                </span>

                {{ $segment['label'] }}
            </div>
            @endforeach
        </div>

        <div class="mp-chart-key">
            <span>■ Plan</span>
            <span>■ Selesai</span>
        </div>

        <div class="mp-chart-scroll">
            <div class="mp-chart-grid">

                @foreach($chartData['days'] as $day)
                @php
                $planHeight = $day['plan_total'] > 0
                ? max(
                3,
                (int) round(
                ($day['plan_total'] / $chartMax) * 145
                )
                )
                : 0;

                $actualHeight = (
                $day['actual_total'] !== null
                && $day['actual_total'] > 0
                )
                ? max(
                3,
                (int) round(
                ($day['actual_total'] / $chartMax) * 145
                )
                )
                : 0;
                @endphp

                <div class="mp-chart-day {{ $day['is_today'] ? 'today' : '' }}">

                    <div class="mp-chart-bars">

                        <div class="mp-chart-column">
                            @if($planHeight > 0)
                            <div
                                class="mp-chart-stack"
                                style="height: {{ $planHeight }}px;">

                                <span class="mp-chart-total">
                                    {{ $day['plan_total'] }}
                                </span>

                                @foreach($chartSegments as $segment)
                                @php
                                $count = (int) (
                                $day['plan'][$segment['key']] ?? 0
                                );

                                $height = $count > 0
                                ? max(
                                2,
                                (int) round(
                                ($count / $chartMax) * 145
                                )
                                )
                                : 0;
                                @endphp

                                @if($height > 0)
                                <div
                                    class="mp-chart-segment"
                                    style="
                                        height: {{ $height }}px;
                                        background: {{ $segment['color'] }};
                                    "
                                    title="{{ $segment['label'] }}: {{ $count }}">

                                    @if($height >= 15)
                                    {{ $count }}
                                    @endif
                                </div>
                                @endif
                                @endforeach

                            </div>
                            @else
                            <span class="mp-chart-empty">
                                <span>0</span>
                            </span>
                            @endif

                            <span class="mp-chart-column-label">
                                Plan
                            </span>
                        </div>

                        <div class="mp-chart-column">
                            @if($day['has_realization'] && $actualHeight > 0)
                            <div
                                class="mp-chart-stack"
                                style="height: {{ $actualHeight }}px;">

                                <span class="mp-chart-total">
                                    {{ $day['actual_total'] }}
                                </span>

                                @foreach($chartSegments as $segment)
                                @php
                                $count = (int) (
                                $day['actual'][$segment['key']] ?? 0
                                );

                                $height = $count > 0
                                ? max(
                                2,
                                (int) round(
                                ($count / $chartMax) * 145
                                )
                                )
                                : 0;
                                @endphp

                                @if($height > 0)
                                <div
                                    class="mp-chart-segment"
                                    style="
                                        height: {{ $height }}px;
                                        background: {{ $segment['color'] }};
                                    "
                                    title="{{ $segment['label'] }}: {{ $count }}">

                                    @if($height >= 15)
                                    {{ $count }}
                                    @endif
                                </div>
                                @endif
                                @endforeach

                            </div>
                            @elseif($day['has_realization'])
                            <span class="mp-chart-empty">
                                <span>0</span>
                            </span>
                            @else
                            <span class="mp-chart-empty">
                                <span>–</span>
                            </span>
                            @endif

                            <span class="mp-chart-column-label">
                                Selesai
                            </span>
                        </div>

                    </div>

                    <div class="mp-chart-date">
                        {{ $day['day'] }}
                    </div>

                    <div class="mp-chart-status">
                        @if($day['is_today'])
                        Hari ini
                        @elseif($day['has_realization'] && $day['gap'] > 0)
                        Kurang {{ $day['gap'] }}
                        @elseif($day['has_realization'] && $day['surplus'] > 0)
                        Lebih {{ $day['surplus'] }}
                        @elseif($day['has_realization'])
                        Terpenuhi
                        @else
                        Belum input
                        @endif
                    </div>

                </div>
                @endforeach

            </div>
        </div>

        <div class="mp-section-subtitle" style="margin-top:8px;">
            Geser grafik ke samping untuk melihat seluruh tanggal.
            Angka di atas batang merupakan total manpower harian.
        </div>
    </section>

    <section class="mp-card">
        <div class="mp-section-heading">
            <div>
                <div class="mp-section-title">
                    Input Realisasi Manpower
                </div>

                <div class="mp-section-subtitle">
                    {{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}
                </div>
            </div>

            @if($realization)
            <span class="badge badge-green">Sudah Diinput</span>
            @else
            <span class="badge badge-gray">Belum Diinput</span>
            @endif
        </div>

        <div class="mp-realization-summary">

            <div class="mp-summary-item">
                <div class="mp-summary-label">
                    Plan Manpower/Hari
                </div>

                <div class="mp-summary-value">
                    {{ number_format($plannedTotal, 0, ',', '.') }}
                </div>
            </div>

            <div class="mp-summary-item">
                <div class="mp-summary-label">
                    Selesai/Hari
                </div>

                <div class="mp-summary-value">
                    {{ $realizationTotal !== null
                        ? number_format($realizationTotal, 0, ',', '.')
                        : '-' }}
                </div>
            </div>

            <div class="mp-summary-item">
                <div class="mp-summary-label">
                    {{ $realizationGap !== null && $realizationGap > 0
                        ? 'Kekurangan'
                        : 'Status' }}
                </div>

                <div class="mp-summary-value
                    {{ $realizationGap !== null && $realizationGap > 0
                        ? 'text-danger'
                        : 'text-green' }}">

                    @if($realizationTotal === null)
                    Belum input
                    @elseif($realizationGap > 0)
                    Kurang {{ $realizationGap }}
                    @elseif($realizationSurplus > 0)
                    Lebih {{ $realizationSurplus }}
                    @else
                    Terpenuhi
                    @endif
                </div>
            </div>

        </div>

        <form id="mpRealizationForm">
            <input
                type="hidden"
                name="work_date"
                value="{{ $date }}">

            <div class="mp-realization-grid">

                <div class="form-group">
                    <label class="form-label" for="actual-os">
                        OS/Hari
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="actual-os"
                        name="os_per_day"
                        min="0"
                        step="1"
                        required
                        value="{{ $realization?->os_per_day }}"
                        placeholder="Plan: {{ $data['os_per_day'] }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="actual-tk">
                        TK/Hari
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="actual-tk"
                        name="tk_per_day"
                        min="0"
                        step="1"
                        required
                        value="{{ $realization?->tk_per_day }}"
                        placeholder="Plan: {{ $data['tk_per_day'] }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="actual-magang">
                        Magang/Hari
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="actual-magang"
                        name="magang_per_day"
                        min="0"
                        step="1"
                        required
                        value="{{ $realization?->magang_per_day }}"
                        placeholder="Plan: {{ $data['magang_per_day'] }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="actual-nonshift">
                        Tambahan Non-Shift/Hari
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="actual-nonshift"
                        name="non_shift_assistance_per_day"
                        min="0"
                        step="1"
                        required
                        value="{{ $realization?->non_shift_assistance_per_day }}"
                        placeholder="Plan: {{ $data['non_shift_assistance_per_day'] }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="actual-additional">
                        Additional/Hari
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="actual-additional"
                        name="additional_manpower_per_day"
                        min="0"
                        step="1"
                        required
                        value="{{ $realization?->additional_manpower_per_day }}"
                        placeholder="Plan: {{ $data['additional_manpower_per_day'] }}">
                </div>

            </div>

            <div class="mp-form-footer">
                <button
                    type="submit"
                    class="btn btn-primary"
                    id="saveRealizationBtn">
                    Simpan Realisasi
                </button>
            </div>
        </form>
    </section>

    <section>
        <div class="mp-section-heading">
            <div>
                <div class="mp-section-title">
                    Order dan Kebutuhan Manpower per Mesin
                </div>

                <div class="mp-section-subtitle">
                    {{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}
                </div>
            </div>

            <span class="badge badge-blue">
                {{ count($data['machines']) }} mesin
            </span>
        </div>

        <div class="mp-card" style="padding:0;overflow:hidden;">

            @if(count($data['machines']) > 0)

            <div class="mp-table-scroll">
                <table class="mp-machine-table">
                    <thead>
                        <tr>
                            <th>Mesin</th>
                            <th>Tipe</th>
                            <th>Order Aktif</th>
                            <th class="text-right">Ideal/Shift</th>
                            <th class="text-right">Override</th>
                            <th class="text-right">Digunakan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data['machines'] as $machine)
                        <tr>
                            <td>
                                <div class="font-semibold">
                                    {{ $machine['machine_code'] }}
                                </div>

                                @if(!empty($machine['machine_name']))
                                <div
                                    class="text-muted"
                                    style="font-size:11px;margin-top:2px;">
                                    {{ $machine['machine_name'] }}
                                </div>
                                @endif
                            </td>

                            <td>
                                <span class="badge
                                    {{ $machine['machine_type'] === 'AUTO'
                                        ? 'badge-green'
                                        : 'badge-blue' }}">
                                    {{ $machine['machine_type'] }}
                                </span>
                            </td>

                            <td>
                                <div class="mp-order-list">
                                    @forelse($machine['orders'] as $order)
                                    <div class="mp-order-item">
                                        @if($order['is_stop'])
                                        <span class="badge badge-red">STOP</span>
                                        @else
                                        {{ $order['order_no'] ?: $order['label'] ?: '-' }}
                                        @if($order['is_special'])
                                        <span class="badge badge-yellow">Khusus</span>
                                        @endif
                                        @endif
                                    </div>
                                    @empty
                                    <span class="text-muted">-</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="num text-right">
                                @if($machine['is_stop'])
                                <span class="badge badge-red">STOP</span>
                                @elseif($machine['default_mp'] !== null)
                                {{ number_format($machine['default_mp'], 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </td>

                            <td
                                class="num text-right"
                                id="override-display-{{ $machine['machine_id'] }}">

                                @if($machine['override_mp'] !== null)
                                <span class="badge badge-yellow">
                                    {{ number_format($machine['override_mp'], 0, ',', '.') }}
                                </span>
                                @else
                                -
                                @endif
                            </td>

                            <td
                                class="num text-right font-semibold"
                                id="used-display-{{ $machine['machine_id'] }}">

                                {{ number_format($machine['mp_used'], 0, ',', '.') }}
                            </td>

                            <td>
                                @if($machine['is_stop'])
                                <span class="badge badge-red">STOP</span>
                                @elseif(!$machine['has_schedule'])
                                <span class="badge badge-gray">Tidak ada jadwal</span>
                                @elseif($machine['override_mp'] !== null)
                                <span class="badge badge-yellow">Override</span>
                                @else
                                <span class="badge badge-green">Default</span>
                                @endif
                            </td>

                            <td>
                                <div class="mp-machine-action">
                                    <input
                                        type="number"
                                        class="form-control mp-machine-input"
                                        id="input-{{ $machine['machine_id'] }}"
                                        value="{{ $machine['override_mp'] !== null
                                            ? $machine['override_mp']
                                            : ($machine['default_mp'] !== null
                                                ? $machine['default_mp']
                                                : 0) }}"
                                        min="0"
                                        step="1"
                                        data-machine-id="{{ $machine['machine_id'] }}">

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary save-btn"
                                        data-machine-id="{{ $machine['machine_id'] }}">
                                        Simpan
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary reset-btn
                                            {{ $machine['override_mp'] === null
                                                ? 'hidden'
                                                : '' }}"
                                        data-machine-id="{{ $machine['machine_id'] }}">
                                        Reset
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @else
            <div class="empty-state">
                <h3>Tidak ada mesin aktif</h3>
                <p>Belum ada mesin yang dapat dihitung pada tanggal ini.</p>
            </div>
            @endif

        </div>
    </section>

    <section class="mp-card mp-params-card">
        <div class="mp-section-heading">
            <div>
                <div class="mp-section-title">
                    Parameter Manpower Harian
                </div>

                <div class="mp-section-subtitle">
                    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                </div>
            </div>

            @if($data['has_custom_params'])
            <span class="badge badge-yellow">Kustom</span>
            @else
            <span class="badge badge-gray">Default</span>
            @endif
        </div>

        <div class="mp-params-grid">
            <div class="form-group">
                <label class="form-label" for="param-tk">
                    TK/Shift
                </label>

                <input
                    type="number"
                    id="param-tk"
                    class="form-control"
                    value="{{ $data['daily_params']['tk_per_shift'] }}"
                    min="0"
                    step="1">
            </div>

            <div class="form-group">
                <label class="form-label" for="param-magang">
                    Magang/Shift
                </label>

                <input
                    type="number"
                    id="param-magang"
                    class="form-control"
                    value="{{ $data['daily_params']['magang_per_shift'] }}"
                    min="0"
                    step="1">
            </div>

            <div class="form-group">
                <label class="form-label" for="param-nonshift">
                    Non-Shift Assistance/Hari
                </label>

                <input
                    type="number"
                    id="param-nonshift"
                    class="form-control"
                    value="{{ $data['daily_params']['non_shift_assistance_per_day'] }}"
                    min="0"
                    step="1">
            </div>

            <div class="form-group">
                <label class="form-label" for="param-additional">
                    Additional Manpower/Hari
                </label>

                <input
                    type="number"
                    id="param-additional"
                    class="form-control"
                    value="{{ $data['daily_params']['additional_manpower_per_day'] }}"
                    min="0"
                    step="1">
            </div>
        </div>

        <div class="mp-form-footer">
            <span class="text-muted" style="font-size:11px;">
                Parameter berlaku untuk tanggal yang dipilih.
            </span>

            <button
                type="button"
                class="btn btn-primary"
                id="saveParamsBtn">
                Simpan Parameter
            </button>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    (function() {
        const page = document.getElementById('mpPage');

        if (!page) {
            return;
        }

        const workDate = page.dataset.workDate;
        const overrideUrl = page.dataset.overrideUrl;
        const resetUrl = page.dataset.resetUrl;
        const paramsUrl = page.dataset.paramsUrl;
        const realizationUrl = page.dataset.realizationUrl;

        const formatNumber = (value) => {
            return Math.ceil(Number(value) || 0).toLocaleString('id-ID');
        };

        function getHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            };
        }

        async function parseResponse(response) {
            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || 'Permintaan gagal diproses.'
                );
            }

            return data;
        }

        function getMachineInput(machineId) {
            return document.getElementById('input-' + machineId);
        }

        function getMachineInputValue(machineId) {
            const input = getMachineInput(machineId);

            if (!input || input.value.trim() === '') {
                return null;
            }

            const value = Number(input.value);

            return Number.isInteger(value) && value >= 0 ?
                value :
                null;
        }

        function setMachineButtonsDisabled(machineId, disabled) {
            document.querySelectorAll(
                '.save-btn[data-machine-id="' + machineId + '"], ' +
                '.reset-btn[data-machine-id="' + machineId + '"]'
            ).forEach((button) => {
                button.disabled = disabled;

                if (!disabled) {
                    button.textContent = button.classList.contains('save-btn') ?
                        'Simpan' :
                        'Reset';
                }
            });
        }

        async function saveOverride(machineId, button) {
            const value = getMachineInputValue(machineId);

            if (value === null) {
                showToast(
                    'MP harus berupa bilangan bulat nol atau lebih.',
                    'error'
                );
                return;
            }

            setMachineButtonsDisabled(machineId, true);
            button.innerHTML = '<span class="spinner"></span>';

            try {
                const response = await fetch(overrideUrl, {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        machine_id: machineId,
                        work_date: workDate,
                        mp_override: value
                    })
                });

                const data = await parseResponse(response);

                if (!data.success) {
                    throw new Error(
                        data.message || 'Gagal menyimpan override.'
                    );
                }

                showToast(
                    'Override berhasil disimpan.',
                    'success'
                );

                window.location.reload();
            } catch (error) {
                showToast(
                    error.message || 'Terjadi kesalahan koneksi.',
                    'error'
                );

                setMachineButtonsDisabled(machineId, false);
            }
        }

        async function resetOverride(machineId) {
            if (!confirm('Reset MP ke nilai default untuk mesin ini?')) {
                return;
            }

            setMachineButtonsDisabled(machineId, true);

            try {
                const response = await fetch(resetUrl, {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        machine_id: machineId,
                        work_date: workDate
                    })
                });

                const data = await parseResponse(response);

                if (!data.success) {
                    throw new Error(
                        data.message || 'Gagal mereset override.'
                    );
                }

                showToast(
                    'Override berhasil direset.',
                    'success'
                );

                window.location.reload();
            } catch (error) {
                showToast(
                    error.message || 'Terjadi kesalahan koneksi.',
                    'error'
                );

                setMachineButtonsDisabled(machineId, false);
            }
        }

        async function saveRealization(event) {
            event.preventDefault();

            const form = document.getElementById('mpRealizationForm');
            const button = document.getElementById('saveRealizationBtn');

            const body = {
                work_date: workDate,
                os_per_day: Number(form.elements.os_per_day.value),
                tk_per_day: Number(form.elements.tk_per_day.value),
                magang_per_day: Number(form.elements.magang_per_day.value),
                non_shift_assistance_per_day: Number(
                    form.elements.non_shift_assistance_per_day.value
                ),
                additional_manpower_per_day: Number(
                    form.elements.additional_manpower_per_day.value
                )
            };

            const values = [
                body.os_per_day,
                body.tk_per_day,
                body.magang_per_day,
                body.non_shift_assistance_per_day,
                body.additional_manpower_per_day
            ];

            if (
                values.some(
                    (value) => !Number.isInteger(value) || value < 0
                )
            ) {
                showToast(
                    'Isi semua realisasi dengan bilangan bulat nol atau lebih.',
                    'error'
                );
                return;
            }

            button.disabled = true;
            button.innerHTML = '<span class="spinner"></span>';

            try {
                const response = await fetch(realizationUrl, {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify(body)
                });

                const data = await parseResponse(response);

                if (!data.success) {
                    throw new Error(
                        data.message || 'Gagal menyimpan realisasi.'
                    );
                }

                const summary = data.summary;

                let message = 'Realisasi tersimpan. Total selesai: ' +
                    formatNumber(summary.completed_total) +
                    ' orang.';

                if (summary.gap > 0) {
                    message += ' Masih kurang ' +
                        formatNumber(summary.gap) +
                        ' orang.';
                } else if (summary.surplus > 0) {
                    message += ' Kelebihan ' +
                        formatNumber(summary.surplus) +
                        ' orang.';
                } else {
                    message += ' Kebutuhan terpenuhi.';
                }

                showToast(message, 'success');
                window.location.reload();
            } catch (error) {
                showToast(
                    error.message || 'Terjadi kesalahan koneksi.',
                    'error'
                );

                button.disabled = false;
                button.textContent = 'Simpan Realisasi';
            }
        }

        async function saveParams() {
            const button = document.getElementById('saveParamsBtn');

            const body = {
                work_date: workDate,
                tk_per_shift: Number(
                    document.getElementById('param-tk').value
                ),
                magang_per_shift: Number(
                    document.getElementById('param-magang').value
                ),
                non_shift_assistance_per_day: Number(
                    document.getElementById('param-nonshift').value
                ),
                additional_manpower_per_day: Number(
                    document.getElementById('param-additional').value
                )
            };

            const values = [
                body.tk_per_shift,
                body.magang_per_shift,
                body.non_shift_assistance_per_day,
                body.additional_manpower_per_day
            ];

            if (
                values.some(
                    (value) => !Number.isInteger(value) || value < 0
                )
            ) {
                showToast(
                    'Semua parameter harus berupa bilangan bulat nol atau lebih.',
                    'error'
                );
                return;
            }

            button.disabled = true;
            button.innerHTML = '<span class="spinner"></span>';

            try {
                const response = await fetch(paramsUrl, {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify(body)
                });

                const data = await parseResponse(response);

                if (!data.success) {
                    throw new Error(
                        data.message || 'Gagal menyimpan parameter.'
                    );
                }

                showToast(
                    'Parameter berhasil disimpan.',
                    'success'
                );

                window.location.reload();
            } catch (error) {
                showToast(
                    error.message || 'Terjadi kesalahan koneksi.',
                    'error'
                );

                button.disabled = false;
                button.textContent = 'Simpan Parameter';
            }
        }

        page.addEventListener('click', (event) => {
            const saveButton = event.target.closest('.save-btn');
            const resetButton = event.target.closest('.reset-btn');

            if (saveButton) {
                saveOverride(
                    saveButton.dataset.machineId,
                    saveButton
                );
            }

            if (resetButton) {
                resetOverride(resetButton.dataset.machineId);
            }
        });

        document.getElementById('mpDateFilter')
            ?.addEventListener('change', function() {
                if (this.value) {
                    document
                        .getElementById('mpFilterForm')
                        .requestSubmit();
                }
            });

        document.getElementById('mpRealizationForm')
            ?.addEventListener('submit', saveRealization);

        document.getElementById('saveParamsBtn')
            ?.addEventListener('click', saveParams);
    })();
</script>
@endpush