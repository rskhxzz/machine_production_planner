@extends('layouts.app')

@section('title', 'Histori Jadwal')
@section('page-title', 'Histori Perubahan Jadwal')

@push('styles')
<style>
    .schedule-history-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .history-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .history-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .history-info {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 16px;
    }

    .history-info-item {
        min-width: 120px;
    }

    .history-info-label {
        font-size: 10px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .history-info-value {
        margin-top: 4px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        overflow-wrap: anywhere;
    }

    .history-table {
        min-width: 760px;
    }

    .history-change-data {
        min-width: 150px;
        font-size: 11px;
        line-height: 1.8;
        color: var(--text-muted);
    }

    .history-change-data strong {
        color: var(--text);
        font-weight: 600;
    }

    .history-mobile {
        display: none;
    }

    .history-mobile-card {
        padding: 14px;
        border-bottom: 1px solid var(--border);
    }

    .history-mobile-card:last-child {
        border-bottom: none;
    }

    .history-mobile-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .history-mobile-date {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
    }

    .history-mobile-reason {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 12px;
        overflow-wrap: anywhere;
    }

    .history-comparison {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .history-comparison-item {
        padding: 10px;
        border-radius: 6px;
        background: #F8FAFC;
        min-width: 0;
    }

    .history-comparison-item.new {
        background: #F0FDF4;
    }

    .history-comparison-title {
        font-size: 10px;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 6px;
    }

    .history-comparison-item.new .history-comparison-title {
        color: var(--green-dark);
    }

    .history-comparison-value {
        font-size: 11px;
        color: var(--text);
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .history-pagination {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    @media (max-width: 768px) {
        .history-info {
            gap: 14px;
        }

        .history-info-item {
            flex: 1 1 40%;
        }

        .history-table-container {
            display: none;
        }

        .history-mobile {
            display: block;
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

        .history-comparison {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-history-page">

    <div class="history-toolbar">
        <div class="history-actions">
            <a href="{{ route('schedules.show', $schedule) }}" class="btn btn-secondary btn-sm">
                Kembali ke Detail
            </a>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
                Daftar Jadwal
            </a>
        </div>

        <span class="text-muted" style="font-size:12px;">
            {{ number_format($history->total()) }} riwayat perubahan
        </span>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Informasi Jadwal</span>
        </div>

        <div class="history-info">
            <div class="history-info-item">
                <div class="history-info-label">Mesin</div>
                <div class="history-info-value">
                    {{ $schedule->machine->machine_code }}
                </div>
            </div>

            <div class="history-info-item">
                <div class="history-info-label">Order</div>
                <div class="history-info-value">
                    {{ $schedule->order_no }}
                </div>
            </div>

            <div class="history-info-item">
                <div class="history-info-label">Periode Mulai</div>
                <div class="history-info-value">
                    {{ $schedule->start_date?->format('d M Y') ?? '-' }}
                </div>
            </div>

            <div class="history-info-item">
                <div class="history-info-label">Periode Akhir</div>
                <div class="history-info-value">
                    {{ $schedule->end_date?->format('d M Y') ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Riwayat Perubahan</span>
            <span class="badge badge-blue">
                {{ number_format($history->total()) }} perubahan
            </span>
        </div>

        @if($history->count() > 0)

        <div class="table-container history-table-container">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe Perubahan</th>
                        <th>Keterangan</th>
                        <th>Data Lama</th>
                        <th>Data Baru</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($history as $item)
                    @php
                    $oldData = is_array($item->old_data) ? $item->old_data : [];
                    $newData = is_array($item->new_data) ? $item->new_data : [];

                    $changeClass = match($item->change_type) {
                    'CREATE' => 'badge-green',
                    'DELETE' => 'badge-red',
                    'SHIFT' => 'badge-blue',
                    default => 'badge-yellow',
                    };
                    @endphp

                    <tr>
                        <td class="nowrap">
                            <div class="font-semibold">
                                {{ $item->changed_at?->format('d M Y') ?? '-' }}
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $item->changed_at?->format('H:i') ?? '' }}
                            </div>
                        </td>

                        <td>
                            <span class="badge {{ $changeClass }}">
                                {{ $item->change_type }}
                            </span>
                        </td>

                        <td style="max-width:200px;font-size:12px;color:var(--text-muted);">
                            {{ $item->change_reason ?: '-' }}
                        </td>

                        <td>
                            @if(count($oldData) > 0)
                            <div class="history-change-data">
                                @if(isset($oldData['start_date']))
                                <div><strong>Start:</strong> {{ $oldData['start_date'] }}</div>
                                @endif

                                @if(isset($oldData['end_date']))
                                <div><strong>End:</strong> {{ $oldData['end_date'] }}</div>
                                @endif

                                @if(isset($oldData['order_no']))
                                <div><strong>Order:</strong> {{ $oldData['order_no'] }}</div>
                                @endif

                                @if(isset($oldData['mp_ideal_per_shift']))
                                <div><strong>MP:</strong> {{ $oldData['mp_ideal_per_shift'] }}</div>
                                @endif

                                @if(isset($oldData['note']))
                                <div><strong>Note:</strong> {{ $oldData['note'] }}</div>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if(count($newData) > 0)
                            <div class="history-change-data">
                                @if(isset($newData['start_date']))
                                <div><strong>Start:</strong> {{ $newData['start_date'] }}</div>
                                @endif

                                @if(isset($newData['end_date']))
                                <div><strong>End:</strong> {{ $newData['end_date'] }}</div>
                                @endif

                                @if(isset($newData['order_no']))
                                <div><strong>Order:</strong> {{ $newData['order_no'] }}</div>
                                @endif

                                @if(isset($newData['mp_ideal_per_shift']))
                                <div><strong>MP:</strong> {{ $newData['mp_ideal_per_shift'] }}</div>
                                @endif

                                @if(isset($newData['note']))
                                <div><strong>Note:</strong> {{ $newData['note'] }}</div>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="history-mobile">
            @foreach($history as $item)
            @php
            $oldData = is_array($item->old_data) ? $item->old_data : [];
            $newData = is_array($item->new_data) ? $item->new_data : [];

            $changeClass = match($item->change_type) {
            'CREATE' => 'badge-green',
            'DELETE' => 'badge-red',
            'SHIFT' => 'badge-blue',
            default => 'badge-yellow',
            };
            @endphp

            <div class="history-mobile-card">
                <div class="history-mobile-top">
                    <div class="history-mobile-date">
                        {{ $item->changed_at?->format('d M Y H:i') ?? '-' }}
                    </div>

                    <span class="badge {{ $changeClass }}">
                        {{ $item->change_type }}
                    </span>
                </div>

                <div class="history-mobile-reason">
                    {{ $item->change_reason ?: 'Tidak ada keterangan perubahan.' }}
                </div>

                <div class="history-comparison">
                    <div class="history-comparison-item">
                        <div class="history-comparison-title">DATA LAMA</div>

                        <div class="history-comparison-value">
                            @if(count($oldData) > 0)
                            @if(isset($oldData['start_date']))
                            <div>Start: {{ $oldData['start_date'] }}</div>
                            @endif

                            @if(isset($oldData['end_date']))
                            <div>End: {{ $oldData['end_date'] }}</div>
                            @endif

                            @if(isset($oldData['order_no']))
                            <div>Order: {{ $oldData['order_no'] }}</div>
                            @endif

                            @if(isset($oldData['mp_ideal_per_shift']))
                            <div>MP: {{ $oldData['mp_ideal_per_shift'] }}</div>
                            @endif

                            @if(isset($oldData['note']))
                            <div>Note: {{ $oldData['note'] }}</div>
                            @endif
                            @else
                            -
                            @endif
                        </div>
                    </div>

                    <div class="history-comparison-item new">
                        <div class="history-comparison-title">DATA BARU</div>

                        <div class="history-comparison-value">
                            @if(count($newData) > 0)
                            @if(isset($newData['start_date']))
                            <div>Start: {{ $newData['start_date'] }}</div>
                            @endif

                            @if(isset($newData['end_date']))
                            <div>End: {{ $newData['end_date'] }}</div>
                            @endif

                            @if(isset($newData['order_no']))
                            <div>Order: {{ $newData['order_no'] }}</div>
                            @endif

                            @if(isset($newData['mp_ideal_per_shift']))
                            <div>MP: {{ $newData['mp_ideal_per_shift'] }}</div>
                            @endif

                            @if(isset($newData['note']))
                            <div>Note: {{ $newData['note'] }}</div>
                            @endif
                            @else
                            -
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <h3>Belum ada histori perubahan</h3>
            <p>Perubahan jadwal akan tercatat dan ditampilkan di sini.</p>
        </div>
        @endif
    </div>

    <div class="history-pagination">
        {{ $history->links('components.pagination') }}
    </div>

</div>
@endsection