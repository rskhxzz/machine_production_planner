@extends('layouts.app')

@section('title', 'Riwayat Import MES')
@section('page-title', 'Riwayat Import MES')

@push('styles')
<style>
    .history-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .history-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .history-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .history-summary {
        color: var(--text-muted);
        font-size: 12px;
    }

    .history-table {
        min-width: 850px;
    }

    .history-mobile {
        display: none;
    }

    .history-card {
        padding: 14px;
        border-bottom: 1px solid var(--border);
    }

    .history-card:last-child {
        border-bottom: none;
    }

    .history-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
    }

    .history-file {
        font-size: 13px;
        font-weight: 600;
        overflow-wrap: anywhere;
        color: var(--text);
    }

    .history-date {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
    }

    .history-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
        background: #F8FAFC;
        border-radius: 6px;
    }

    .history-stat-label {
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .history-stat-value {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        font-variant-numeric: tabular-nums;
    }

    .history-error {
        margin-top: 10px;
        padding: 9px 10px;
        border-radius: 6px;
        background: #FEF2F2;
        color: #B91C1C;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .history-error-label {
        font-weight: 600;
        margin-bottom: 2px;
    }

    @media (max-width: 768px) {
        .history-table-container {
            display: none;
        }

        .history-mobile {
            display: block;
        }

        .history-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 420px) {
        .history-actions {
            width: 100%;
        }

        .history-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="history-page">

    <div class="history-toolbar">
        <div class="history-actions">
            <a href="{{ route('mes.import') }}" class="btn btn-primary btn-sm">
                + Import Baru
            </a>

            <a href="{{ route('mes.index') }}" class="btn btn-secondary btn-sm">
                Aktual Produksi
            </a>
        </div>

        <div class="history-summary">
            {{ number_format($batches->total()) }} riwayat import
        </div>
    </div>

    <div class="card">
        @if($batches->count() > 0)

        <div class="table-container history-table-container">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Tipe</th>
                        <th>Tanggal Import</th>
                        <th class="text-right">Tgl Terdampak</th>
                        <th class="text-right">Data Diarsip</th>
                        <th class="text-right">Data Baru</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    @php
                    $statusClass = match($batch->status) {
                    'SUCCESS' => 'badge-green',
                    'FAILED' => 'badge-red',
                    default => 'badge-yellow',
                    };
                    @endphp

                    <tr>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;" title="{{ $batch->file_name }}">
                            <span class="font-semibold">{{ $batch->file_name }}</span>
                        </td>

                        <td>
                            <span class="badge badge-blue">{{ $batch->import_type }}</span>
                        </td>

                        <td class="nowrap text-muted">
                            {{ $batch->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>

                        <td class="num text-right">
                            {{ is_array($batch->dates_in_file) ? count($batch->dates_in_file) : 0 }}
                        </td>

                        <td class="num text-right">
                            {{ number_format($batch->deleted_rows) }}
                        </td>

                        <td class="num text-right font-semibold">
                            {{ number_format($batch->inserted_rows) }}
                        </td>

                        <td>
                            <span class="badge {{ $statusClass }}">
                                {{ $batch->status }}
                            </span>
                        </td>

                        <td style="max-width:200px;font-size:12px;color:var(--text-muted);">
                            {{ $batch->error_message ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="history-mobile">
            @foreach($batches as $batch)
            @php
            $statusClass = match($batch->status) {
            'SUCCESS' => 'badge-green',
            'FAILED' => 'badge-red',
            default => 'badge-yellow',
            };
            @endphp

            <div class="history-card">
                <div class="history-card-top">
                    <div style="min-width:0;">
                        <div class="history-file">{{ $batch->file_name }}</div>
                        <div class="history-date">
                            {{ $batch->created_at?->format('d M Y, H:i') ?? '-' }}
                        </div>
                    </div>

                    <span class="badge {{ $statusClass }}">
                        {{ $batch->status }}
                    </span>
                </div>

                <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
                    <span class="badge badge-blue">{{ $batch->import_type }}</span>
                    <span class="text-muted" style="font-size:11px;">
                        {{ is_array($batch->dates_in_file) ? count($batch->dates_in_file) : 0 }} tanggal terdampak
                    </span>
                </div>

                <div class="history-stats">
                    <div>
                        <div class="history-stat-label">Data Diarsip</div>
                        <div class="history-stat-value">
                            {{ number_format($batch->deleted_rows) }}
                        </div>
                    </div>

                    <div>
                        <div class="history-stat-label">Data Baru</div>
                        <div class="history-stat-value">
                            {{ number_format($batch->inserted_rows) }}
                        </div>
                    </div>
                </div>

                @if($batch->error_message)
                <div class="history-error">
                    <div class="history-error-label">Keterangan</div>
                    {{ $batch->error_message }}
                </div>
                @endif
            </div>
            @endforeach
        </div>

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>Belum ada riwayat import</h3>
            <p>Riwayat import DATA MES akan ditampilkan di sini.</p>
            <a href="{{ route('mes.import') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">
                Import Data MES
            </a>
        </div>
        @endif
    </div>

    <div style="display:flex;justify-content:center;width:100%;">
        {{ $batches->links('components.pagination') }}
    </div>

</div>
@endsection