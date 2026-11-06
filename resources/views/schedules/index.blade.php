@extends('layouts.app')

@section('title', 'Jadwal Produksi')
@section('page-title', 'Jadwal Produksi')

@push('styles')
<style>
    .schedule-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .schedule-filter {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr)) auto;
        gap: 12px;
        align-items: end;
        padding: 16px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .schedule-filter .form-group {
        margin: 0;
        min-width: 0;
    }

    .schedule-filter-actions {
        display: flex;
        gap: 8px;
    }

    .schedule-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .schedule-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .schedule-count {
        color: var(--text-muted);
        font-size: 12px;
    }

    .schedule-table {
        min-width: 1050px;
    }

    .schedule-table td {
        white-space: nowrap;
    }

    .schedule-mobile {
        display: none;
    }

    .schedule-mobile-card {
        padding: 14px;
        border-bottom: 1px solid var(--border);
    }

    .schedule-mobile-card:last-child {
        border-bottom: none;
    }

    .schedule-mobile-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
    }

    .schedule-machine {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .schedule-order {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        margin-top: 4px;
        overflow-wrap: anywhere;
    }

    .schedule-note {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
        overflow-wrap: anywhere;
    }

    .schedule-mobile-info {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
        background: #F8FAFC;
        border-radius: 6px;
    }

    .schedule-info-label {
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 3px;
    }

    .schedule-info-value {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        font-variant-numeric: tabular-nums;
        overflow-wrap: anywhere;
    }

    .schedule-mobile-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .schedule-mobile-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .schedule-delete-form {
        display: inline;
        margin: 0;
    }

    .schedule-pagination {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    @media (max-width: 1200px) {
        .schedule-filter {
            grid-template-columns: repeat(3, minmax(130px, 1fr));
        }

        .schedule-filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 768px) {
        .schedule-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: 12px;
        }

        .schedule-filter-actions {
            grid-column: 1 / -1;
        }

        .schedule-filter-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .schedule-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .schedule-actions {
            width: 100%;
        }

        .schedule-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .schedule-table-container {
            display: none;
        }

        .schedule-mobile {
            display: block;
        }
    }

    @media (max-width: 480px) {
        .schedule-filter {
            grid-template-columns: 1fr;
        }

        .schedule-filter-actions {
            grid-column: auto;
        }

        .schedule-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .schedule-count {
            align-self: flex-start;
        }

        .schedule-mobile-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .schedule-mobile-actions {
            width: 100%;
        }

        .schedule-mobile-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-page">

    <form method="GET" action="{{ route('schedules.index') }}" class="schedule-filter">
        <div class="form-group">
            <label class="form-label">Mesin</label>
            <select name="machine_id" class="form-control">
                <option value="">Semua Mesin</option>
                @foreach($machines as $machine)
                <option value="{{ $machine->id }}" {{ request('machine_id') == $machine->id ? 'selected' : '' }}>
                    {{ $machine->machine_code }} ({{ $machine->machine_type }})
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>
                    Aktif
                </option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Periode Mulai</label>
            <input
                type="date"
                name="start_date"
                value="{{ request('start_date') }}"
                class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Periode Akhir</label>
            <input
                type="date"
                name="end_date"
                value="{{ request('end_date') }}"
                class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Cari Order / Note</label>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="Masukkan kata kunci...">
        </div>

        <div class="schedule-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">
                Filter
            </button>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
                Reset
            </a>
        </div>
    </form>

    <div class="schedule-toolbar">
        <div class="schedule-actions">
            <a href="{{ route('schedules.create') }}" class="btn btn-primary btn-sm">
                + Tambah Jadwal
            </a>

            <a href="{{ route('schedules.import') }}" class="btn btn-secondary btn-sm">
                Import Excel
            </a>

            <a href="{{ route('schedules.shift.preview') }}" class="btn btn-secondary btn-sm">
                Geser Jadwal
            </a>
        </div>

        <div class="schedule-count">
            {{ number_format($schedules->total()) }} jadwal ditemukan
        </div>
    </div>

    <div class="card">
        @if($schedules->count() > 0)

        <div class="table-container schedule-table-container">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Mesin</th>
                        <th>Order</th>
                        <th>Jenis Jadwal</th>
                        <th>Note</th>
                        <th>Start</th>
                        <th>End</th>
                        <th class="text-right">MT</th>
                        <th class="text-right">Capacity</th>
                        <th class="text-right">Qty PCS</th>
                        <th class="text-right">MP/Shift</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($schedules as $schedule)
                    @php
                    $isStop = $schedule->isStopOrder();
                    @endphp

                    <tr>
                        <td>
                            <span class="badge {{ $schedule->machine->machine_type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                                {{ $schedule->machine->machine_code }}
                            </span>
                        </td>

                        <td class="font-semibold">
                            {{ $schedule->order_no }}
                        </td>

                        <td>
                            @if($schedule->is_special)
                            <span class="badge badge-yellow">Khusus</span>
                            <div class="text-muted" style="font-size:10px;margin-top:4px;">
                                Shift {{ implode(', ', $schedule->special_shifts ?? []) }}
                            </div>
                            @else
                            <span class="badge badge-blue">Reguler</span>
                            @endif
                        </td>

                        <td class="text-muted">
                            {{ $schedule->note ?? '-' }}
                        </td>

                        <td>
                            {{ $schedule->start_date?->format('d/m/Y') ?? '-' }}
                        </td>

                        <td>
                            {{ $schedule->end_date?->format('d/m/Y') ?? '-' }}
                        </td>

                        <td class="num text-right">
                            {{ $schedule->mt !== null ? number_format($schedule->mt, 2) : '-' }}
                        </td>

                        <td class="num text-right">
                            {{ $schedule->capacity !== null ? number_format($schedule->capacity, 0) : '-' }}
                        </td>

                        <td class="num text-right">
                            {{ $schedule->qty_pcs !== null ? number_format($schedule->qty_pcs, 0) : '-' }}
                        </td>

                        <td class="num text-right font-semibold">
                            {{ $schedule->mp_ideal_per_shift !== null ? number_format($schedule->mp_ideal_per_shift, 0) : '-' }}
                        </td>

                        <td>
                            @if($isStop)
                            <span class="badge badge-red">STOP</span>
                            @else
                            <span class="badge badge-green">Aktif</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-8">
                                <a
                                    href="{{ route('schedules.show', $schedule) }}"
                                    class="btn btn-sm btn-secondary">
                                    Detail
                                </a>

                                <a
                                    href="{{ route('schedules.edit', $schedule) }}"
                                    class="btn btn-sm btn-secondary">
                                    Edit
                                </a>

                                <form
                                    id="delete-schedule-{{ $schedule->id }}"
                                    method="POST"
                                    action="{{ route('schedules.destroy', ['schedule' => $schedule->id]) }}"
                                    class="schedule-delete-form">

                                    @csrf
                                    @method('DELETE')
                                </form>

                                <button
                                    type="button"
                                    onclick="confirmDelete('delete-schedule-{{ $schedule->id }}')"
                                    class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="schedule-mobile">
            @foreach($schedules as $schedule)
            @php
            $isStop = $schedule->isStopOrder();
            @endphp

            <div class="schedule-mobile-card">
                <div class="schedule-mobile-top">
                    <div style="min-width:0;">
                        <div class="schedule-machine">
                            {{ $schedule->machine->machine_code }}
                            <span class="badge {{ $schedule->machine->machine_type === 'AUTO' ? 'badge-green' : 'badge-blue' }}">
                                {{ $schedule->machine->machine_type }}
                            </span>
                        </div>

                        <div class="schedule-order">
                            {{ $schedule->order_no }}
                        </div>

                        <div class="schedule-note">
                            {{ $schedule->note ?? 'Tidak ada keterangan' }}
                        </div>
                    </div>

                    @if($isStop)
                    <span class="badge badge-red">STOP</span>
                    @else
                    <span class="badge badge-green">Aktif</span>
                    @endif
                </div>

                <div class="schedule-mobile-info">
                    <div>
                        <div class="schedule-info-label">Periode Mulai</div>
                        <div class="schedule-info-value">
                            {{ $schedule->start_date?->format('d M Y') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="schedule-info-label">Periode Akhir</div>
                        <div class="schedule-info-value">
                            {{ $schedule->end_date?->format('d M Y') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="schedule-info-label">Capacity / Qty PCS</div>
                        <div class="schedule-info-value">
                            {{ $schedule->capacity !== null ? number_format($schedule->capacity, 0) : '-' }}
                            /
                            {{ $schedule->qty_pcs !== null ? number_format($schedule->qty_pcs, 0) : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="schedule-info-label">MP Ideal/Shift</div>
                        <div class="schedule-info-value">
                            {{ $schedule->mp_ideal_per_shift !== null ? number_format($schedule->mp_ideal_per_shift, 1) : '-' }}
                        </div>
                    </div>
                </div>

                <div class="schedule-mobile-footer">
                    <span class="text-muted" style="font-size:11px;">
                        ID: {{ $schedule->id }}
                    </span>

                    <div class="schedule-mobile-actions">
                        <a
                            href="{{ route('schedules.show', $schedule) }}"
                            class="btn btn-sm btn-secondary">
                            Detail
                        </a>

                        <a
                            href="{{ route('schedules.edit', $schedule) }}"
                            class="btn btn-sm btn-secondary">
                            Edit
                        </a>

                        <form
                            id="mobile-delete-schedule-{{ $schedule->id }}"
                            method="POST"
                            action="{{ route('schedules.destroy', ['schedule' => $schedule->id]) }}"
                            class="schedule-delete-form">

                            @csrf
                            @method('DELETE')
                        </form>

                        <button
                            type="button"
                            onclick="confirmDelete('mobile-delete-schedule-{{ $schedule->id }}')"
                            class="btn btn-sm btn-danger">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <h3>Belum ada jadwal produksi</h3>
            <p>Tambahkan jadwal baru atau sesuaikan filter pencarian.</p>

            <a href="{{ route('schedules.create') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">
                Tambah Jadwal
            </a>
        </div>
        @endif
    </div>

    <div class="schedule-pagination">
        {{ $schedules->links('components.pagination') }}
    </div>

</div>
@endsection