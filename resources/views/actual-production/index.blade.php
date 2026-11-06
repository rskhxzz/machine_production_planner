@extends('layouts.app')

@section('title', 'Aktual Produksi')
@section('page-title', 'Aktual Produksi MES')

@push('styles')
<style>
    .mes-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .mes-filter {
        display: grid;
        grid-template-columns: repeat(5, minmax(130px, 1fr)) auto;
        gap: 12px;
        align-items: end;
        padding: 16px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .mes-filter .form-group {
        margin-bottom: 0;
        min-width: 0;
    }

    .mes-filter-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .mes-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .mes-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .mes-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        color: var(--text-muted);
        font-size: 12px;
    }

    .mes-table {
        min-width: 1120px;
    }

    .mes-table td {
        white-space: nowrap;
    }

    .mes-customer {
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap !important;
    }

    .mes-mobile-list {
        display: none;
    }

    .mes-mobile-card {
        padding: 14px;
        border-bottom: 1px solid var(--border);
    }

    .mes-mobile-card:last-child {
        border-bottom: none;
    }

    .mes-mobile-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 10px;
    }

    .mes-mobile-order {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        overflow-wrap: anywhere;
    }

    .mes-mobile-sub {
        margin-top: 3px;
        color: var(--text-muted);
        font-size: 11px;
    }

    .mes-mobile-customer {
        color: var(--text-muted);
        font-size: 12px;
        margin-bottom: 12px;
        overflow-wrap: anywhere;
    }

    .mes-mobile-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
        background: #F8FAFC;
        border-radius: 6px;
    }

    .mes-stat-label {
        color: var(--text-muted);
        font-size: 10px;
        margin-bottom: 2px;
    }

    .mes-stat-value {
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        overflow-wrap: anywhere;
    }

    .mes-pagination {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    @media (max-width: 1100px) {
        .mes-filter {
            grid-template-columns: repeat(3, minmax(130px, 1fr));
        }

        .mes-filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 768px) {
        .mes-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: 12px;
        }

        .mes-filter .form-control {
            font-size: 12px;
        }

        .mes-filter-actions {
            grid-column: 1 / -1;
        }

        .mes-filter-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .mes-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .mes-toolbar-actions {
            width: 100%;
        }

        .mes-toolbar-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .mes-table-container {
            display: none;
        }

        .mes-mobile-list {
            display: block;
        }
    }

    @media (max-width: 420px) {
        .mes-filter {
            grid-template-columns: 1fr;
        }

        .mes-filter-actions {
            grid-column: auto;
        }

        .mes-toolbar-actions {
            flex-direction: column;
        }

        .mes-toolbar-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="mes-page">

    <form method="GET" action="{{ route('mes.index') }}" class="mes-filter">
        <div class="form-group">
            <label class="form-label">Periode Mulai</label>
            <input
                type="date"
                name="start_date"
                value="{{ request('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}"
                class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Periode Akhir</label>
            <input
                type="date"
                name="end_date"
                value="{{ request('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')) }}"
                class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Mesin</label>
            <select name="machine" class="form-control">
                <option value="">Semua Mesin</option>
                @foreach($availableMachines as $machine)
                <option value="{{ $machine }}" {{ request('machine') == $machine ? 'selected' : '' }}>
                    {{ $machine }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Shift</label>
            <select name="shift" class="form-control">
                <option value="">Semua Shift</option>
                @foreach($availableShifts as $shift)
                <option value="{{ $shift }}" {{ request('shift') == $shift ? 'selected' : '' }}>
                    Shift {{ $shift }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Cari Order / Customer</label>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="Masukkan kata kunci...">
        </div>

        <div class="mes-filter-actions">
            <button type="submit" class="btn btn-primary">
                Filter
            </button>

            <a href="{{ route('mes.index') }}" class="btn btn-secondary">
                Reset
            </a>
        </div>
    </form>

    <div class="mes-toolbar">
        <div class="mes-toolbar-actions">
            <a href="{{ route('mes.import') }}" class="btn btn-primary btn-sm">
                + Import MES
            </a>

            <a href="{{ route('mes.history') }}" class="btn btn-secondary btn-sm">
                Riwayat Import
            </a>
        </div>

        <div class="mes-meta">
            @if($latestDate)
            <span class="badge badge-green">
                Data terakhir: {{ \Carbon\Carbon::parse($latestDate)->format('d M Y') }}
            </span>
            @endif

            <span>{{ number_format($productions->total()) }} record ditemukan</span>
        </div>
    </div>

    <div class="card">
        @if($productions->count() > 0)

        <div class="table-container mes-table-container">
            <table class="mes-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Regu</th>
                        <th>Mesin</th>
                        <th>Order</th>
                        <th>Route</th>
                        <th>Customer</th>
                        <th class="text-right">Output KG</th>
                        <th class="text-right">Output TON</th>
                        <th class="text-right">Qty Prod</th>
                        <th>UOM</th>
                        <th class="text-right">Waste KG</th>
                        <th class="text-right">Waste %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productions as $prod)
                    @php
                    $kg = (float) ($prod->prd_a ?? 0) + (float) ($prod->prd_b ?? 0);
                    $ton = $kg / 1000;

                    $waste = (float) ($prod->wst_prd ?? 0)
                    + (float) ($prod->sisiran ?? 0)
                    + (float) ($prod->sobekan ?? 0)
                    + (float) ($prod->score ?? 0)
                    + (float) ($prod->wgtgf ?? 0);

                    $bp = (float) ($prod->bp ?? 0);
                    $wastePct = $bp > 0 ? ($waste / $bp * 100) : 0;
                    @endphp

                    <tr>
                        <td class="nowrap">
                            {{ \Carbon\Carbon::parse($prod->datprd)->format('d/m/Y') }}
                        </td>
                        <td class="text-center">{{ $prod->shft ?? '-' }}</td>
                        <td>{{ $prod->regu ?? '-' }}</td>
                        <td>
                            <span class="badge badge-green">{{ $prod->mcnnam }}</span>
                        </td>
                        <td class="font-semibold">{{ $prod->prpno ?? '-' }}</td>
                        <td class="text-muted">{{ $prod->rtenam ?? '-' }}</td>
                        <td class="mes-customer" title="{{ $prod->custnam ?? '' }}">
                            {{ $prod->custnam ?? '-' }}
                        </td>
                        <td class="num text-right">{{ number_format($kg, 2) }}</td>
                        <td class="num text-right font-semibold">{{ number_format($ton, 3) }}</td>
                        <td class="num text-right">
                            {{ $prod->qtyprd !== null ? number_format((float) $prod->qtyprd, 0) : '-' }}
                        </td>
                        <td>{{ $prod->uom ?? '-' }}</td>
                        <td class="num text-right">{{ number_format($waste, 2) }}</td>
                        <td class="num text-right {{ $wastePct > 5 ? 'text-danger' : 'text-green' }}">
                            {{ number_format($wastePct, 1) }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mes-mobile-list">
            @foreach($productions as $prod)
            @php
            $kg = (float) ($prod->prd_a ?? 0) + (float) ($prod->prd_b ?? 0);
            $ton = $kg / 1000;

            $waste = (float) ($prod->wst_prd ?? 0)
            + (float) ($prod->sisiran ?? 0)
            + (float) ($prod->sobekan ?? 0)
            + (float) ($prod->score ?? 0)
            + (float) ($prod->wgtgf ?? 0);

            $bp = (float) ($prod->bp ?? 0);
            $wastePct = $bp > 0 ? ($waste / $bp * 100) : 0;
            @endphp

            <div class="mes-mobile-card">
                <div class="mes-mobile-top">
                    <div style="min-width:0;">
                        <div class="mes-mobile-order">{{ $prod->prpno ?? '-' }}</div>
                        <div class="mes-mobile-sub">
                            {{ \Carbon\Carbon::parse($prod->datprd)->format('d M Y') }}
                            · Shift {{ $prod->shft ?? '-' }}
                            · Regu {{ $prod->regu ?? '-' }}
                        </div>
                    </div>

                    <span class="badge badge-green">{{ $prod->mcnnam }}</span>
                </div>

                <div class="mes-mobile-customer">
                    {{ $prod->custnam ?? '-' }}
                    @if($prod->rtenam)
                    · Route {{ $prod->rtenam }}
                    @endif
                </div>

                <div class="mes-mobile-stats">
                    <div>
                        <div class="mes-stat-label">Output KG</div>
                        <div class="mes-stat-value">{{ number_format($kg, 2) }}</div>
                    </div>
                    <div>
                        <div class="mes-stat-label">Output TON</div>
                        <div class="mes-stat-value">{{ number_format($ton, 3) }}</div>
                    </div>
                    <div>
                        <div class="mes-stat-label">Qty Produksi</div>
                        <div class="mes-stat-value">
                            {{ $prod->qtyprd !== null ? number_format((float) $prod->qtyprd, 0) : '-' }}
                            {{ $prod->uom ?? '' }}
                        </div>
                    </div>
                    <div>
                        <div class="mes-stat-label">Waste KG / %</div>
                        <div class="mes-stat-value {{ $wastePct > 5 ? 'text-danger' : 'text-green' }}">
                            {{ number_format($waste, 2) }} / {{ number_format($wastePct, 1) }}%
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @else
        <div class="empty-state">
            <div class="empty-state-icon">🏭</div>
            <h3>Belum ada data aktual produksi</h3>
            <p>Silakan ubah filter atau import file Excel DATA MES.</p>
            <a href="{{ route('mes.import') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">
                Import Data MES
            </a>
        </div>
        @endif
    </div>

    <div class="mes-pagination">
        {{ $productions->links('components.pagination') }}
    </div>

</div>
@endsection