@extends('layouts.app')

@section('title', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Produksi')

@push('styles')
<style>
    .schedule-detail-page {
        max-width: 900px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .schedule-detail-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .schedule-detail-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .schedule-detail-machine {
        font-size: 17px;
        font-weight: 700;
        color: var(--text);
    }

    .schedule-detail-order {
        margin-top: 3px;
        font-size: 12px;
        color: var(--text-muted);
        overflow-wrap: anywhere;
    }

    .schedule-detail-status {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .schedule-detail-section {
        padding: 16px;
    }

    .schedule-detail-section+.schedule-detail-section {
        border-top: 1px solid var(--border);
    }

    .schedule-detail-section-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 14px;
    }

    .schedule-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px 16px;
    }

    .schedule-detail-item {
        min-width: 0;
    }

    .schedule-detail-label {
        font-size: 10px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 4px;
    }

    .schedule-detail-value {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        overflow-wrap: anywhere;
    }

    .schedule-detail-value.regular {
        font-weight: 400;
    }

    .schedule-detail-value.muted {
        color: var(--text-muted);
    }

    .schedule-detail-history-table {
        min-width: 550px;
    }

    .schedule-detail-history-mobile {
        display: none;
    }

    .schedule-detail-history-item {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
    }

    .schedule-detail-history-item:last-child {
        border-bottom: none;
    }

    .schedule-detail-history-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .schedule-detail-history-date {
        font-size: 12px;
        font-weight: 600;
    }

    .schedule-detail-history-reason {
        font-size: 11px;
        color: var(--text-muted);
        overflow-wrap: anywhere;
    }

    .schedule-detail-footer {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        color: var(--text-muted);
        font-size: 11px;
    }

    @media (max-width: 768px) {
        .schedule-detail-page {
            max-width: none;
        }

        .schedule-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 12px;
        }

        .schedule-detail-history-table-container {
            display: none;
        }

        .schedule-detail-history-mobile {
            display: block;
        }
    }

    @media (max-width: 420px) {
        .schedule-detail-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .schedule-detail-actions .btn {
            justify-content: center;
        }

        .schedule-detail-grid {
            grid-template-columns: 1fr;
            gap: 13px;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-detail-page">

    <div class="schedule-detail-actions">
        <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
            Kembali
        </a>

        <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-primary btn-sm">
            Edit Jadwal
        </a>

        <a href="{{ route('schedules.history', $schedule) }}" class="btn btn-secondary btn-sm">
            Lihat Histori
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="schedule-detail-heading" style="width:100%;">
                <div style="min-width:0;">
                    <div class="schedule-detail-machine">
                        {{ $schedule->machine->machine_code }}
                    </div>

                    <div class="schedule-detail-order">
                        Order: {{ $schedule->order_no }}
                    </div>
                </div>

                <div class="schedule-detail-status">
                    <span class="badge {{ $schedule->machine->machine_type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                        {{ $schedule->machine->machine_type }}
                    </span>

                    @if($schedule->isStopOrder())
                    <span class="badge badge-red">STOP</span>
                    @else
                    <span class="badge badge-green">Aktif</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="schedule-detail-section">
            <div class="schedule-detail-section-title">
                Informasi Mesin dan Order
            </div>

            <div class="schedule-detail-grid">
                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Kode Mesin</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->machine->machine_code }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Nama Mesin</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->machine->machine_name ?? '-' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Tipe Mesin</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->machine->machine_type }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Order Number</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->order_no }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Sequence</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->schedule_sequence ?? '-' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Status</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->isStopOrder() ? 'STOP' : 'Aktif' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Note</div>
                    <div class="schedule-detail-value regular">
                        {{ $schedule->note ?? '-' }}
                    </div>
                </div>
            </div>
            <div class="schedule-detail-item">
                <div class="schedule-detail-label">Jenis Jadwal</div>

                <div class="schedule-detail-value">
                    @if($schedule->is_special)
                    Jadwal Khusus
                    @else
                    Jadwal Reguler
                    @endif
                </div>
            </div>

            @if($schedule->is_special)
            <div class="schedule-detail-item">
                <div class="schedule-detail-label">Shift Aktif</div>

                <div class="schedule-detail-value">
                    {{ implode(', ', $schedule->special_shifts ?? []) }}
                </div>
            </div>
            @endif
        </div>

        <div class="schedule-detail-section">
            <div class="schedule-detail-section-title">
                Periode Produksi
            </div>

            <div class="schedule-detail-grid">
                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Tanggal Mulai</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->start_date?->format('d M Y') ?? '-' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Tanggal Selesai</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->end_date?->format('d M Y') ?? '-' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Durasi Kalender</div>
                    <div class="schedule-detail-value">
                        @if($schedule->start_date && $schedule->end_date)
                        {{ $schedule->start_date->diffInDays($schedule->end_date) + 1 }} hari
                        @else
                        -
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="schedule-detail-section">
            <div class="schedule-detail-section-title">
                Informasi Produksi dan Manpower
            </div>

            <div class="schedule-detail-grid">
                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">MT</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->mt !== null ? number_format($schedule->mt, 3) : '-' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Capacity</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->capacity !== null ? number_format($schedule->capacity, 0) : '-' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">Qty PCS</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->qty_pcs !== null ? number_format($schedule->qty_pcs, 0) : '-' }}
                    </div>
                </div>

                <div class="schedule-detail-item">
                    <div class="schedule-detail-label">MP Ideal/Shift</div>
                    <div class="schedule-detail-value">
                        {{ $schedule->mp_ideal_per_shift !== null ? number_format($schedule->mp_ideal_per_shift, 2) : '-' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="schedule-detail-footer">
            <div>
                Dibuat:
                {{ $schedule->created_at?->format('d M Y H:i') ?? '-' }}
            </div>

            <div>
                Diperbarui:
                {{ $schedule->updated_at?->format('d M Y H:i') ?? '-' }}
            </div>
        </div>
    </div>

    @if($history->count() > 0)
    <div class="card">
        <div class="card-header">
            <span class="card-title">Histori Perubahan Terbaru</span>

            <a href="{{ route('schedules.history', $schedule) }}" class="btn btn-secondary btn-sm">
                Lihat Semua
            </a>
        </div>

        <div class="table-container schedule-detail-history-table-container">
            <table class="schedule-detail-history-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($history->take(5) as $item)
                    @php
                    $changeClass = match($item->change_type) {
                    'CREATE' => 'badge-green',
                    'DELETE' => 'badge-red',
                    'SHIFT' => 'badge-blue',
                    default => 'badge-yellow',
                    };
                    @endphp

                    <tr>
                        <td class="nowrap">
                            {{ $item->changed_at?->format('d M Y H:i') ?? '-' }}
                        </td>

                        <td>
                            <span class="badge {{ $changeClass }}">
                                {{ $item->change_type }}
                            </span>
                        </td>

                        <td class="text-muted">
                            {{ $item->change_reason ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="schedule-detail-history-mobile">
            @foreach($history->take(5) as $item)
            @php
            $changeClass = match($item->change_type) {
            'CREATE' => 'badge-green',
            'DELETE' => 'badge-red',
            'SHIFT' => 'badge-blue',
            default => 'badge-yellow',
            };
            @endphp

            <div class="schedule-detail-history-item">
                <div class="schedule-detail-history-top">
                    <span class="schedule-detail-history-date">
                        {{ $item->changed_at?->format('d M Y H:i') ?? '-' }}
                    </span>

                    <span class="badge {{ $changeClass }}">
                        {{ $item->change_type }}
                    </span>
                </div>

                <div class="schedule-detail-history-reason">
                    {{ $item->change_reason ?: 'Tidak ada keterangan.' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection