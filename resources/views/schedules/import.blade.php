@extends('layouts.app')

@section('title', 'Import Jadwal')
@section('page-title', 'Import Jadwal Produksi')

@push('styles')
<style>
    .schedule-import-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .schedule-import-card {
        max-width: 720px;
    }

    .schedule-import-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .schedule-import-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .schedule-import-info {
        padding: 13px 14px;
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-radius: 7px;
        color: #166534;
        font-size: 12px;
        line-height: 1.7;
        margin-bottom: 14px;
    }

    .schedule-import-warning {
        padding: 13px 14px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-radius: 7px;
        color: #92400E;
        font-size: 12px;
        line-height: 1.7;
        margin-bottom: 18px;
    }

    .schedule-import-columns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .schedule-import-column {
        padding: 12px;
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 7px;
        min-width: 0;
    }

    .schedule-import-column-title {
        font-size: 11px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 7px;
    }

    .schedule-import-column-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .schedule-import-column-list code {
        display: inline-block;
        padding: 3px 7px;
        border-radius: 4px;
        background: #fff;
        border: 1px solid var(--border);
        color: var(--text-muted);
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    .schedule-file-input {
        padding: 12px;
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: 7px;
        cursor: pointer;
    }

    .schedule-file-input:hover {
        background: #F0FDF4;
        border-color: var(--green);
    }

    @media (max-width: 600px) {
        .schedule-import-card {
            max-width: none;
        }

        .schedule-import-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .schedule-import-actions {
            width: 100%;
        }

        .schedule-import-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .schedule-import-columns {
            grid-template-columns: 1fr;
        }

        .schedule-import-submit {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 380px) {
        .schedule-import-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-import-page">

    <div class="schedule-import-toolbar">
        <div class="schedule-import-actions">
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary btn-sm">
                Kembali ke Jadwal
            </a>

            <a href="{{ route('schedules.import.template') }}" class="btn btn-primary btn-sm">
                Download Template Excel
            </a>
        </div>
    </div>

    <div class="card schedule-import-card">
        <div class="card-header">
            <span class="card-title">Upload Jadwal Produksi</span>
        </div>

        <div class="card-body">

            <div class="schedule-import-info">
                <strong>Informasi Import</strong><br>
                Upload file Excel berisi jadwal produksi.
                Sistem akan melakukan preview dan validasi data sebelum jadwal dimasukkan ke database.
                Jadwal yang sudah ada tidak akan dihapus.
            </div>

            <div class="schedule-import-warning">
                <strong>Perhatikan format kolom Excel.</strong>
                Pastikan nama header sesuai dengan template agar data dapat dibaca oleh sistem.
            </div>

            <div class="schedule-import-columns">
                <div class="schedule-import-column">
                    <div class="schedule-import-column-title">
                        <span class="text-danger">●</span> Kolom Wajib
                    </div>

                    <div class="schedule-import-column-list">
                        <code>machine_code</code>
                        <code>order_no</code>
                        <code>start_date</code>
                        <code>end_date</code>
                    </div>
                </div>

                <div class="schedule-import-column">
                    <div class="schedule-import-column-title">
                        <span class="text-muted">●</span> Kolom Opsional
                    </div>

                    <div class="schedule-import-column-list">
                        <code>schedule_sequence</code>
                        <code>note</code>
                        <code>mt</code>
                        <code>capacity</code>
                        <code>qty_pcs</code>
                        <code>mp_ideal_per_shift</code>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('schedules.import.preview') }}"
                enctype="multipart/form-data">

                @csrf

                <div class="form-group">
                    <label class="form-label required">File Excel Jadwal Produksi</label>

                    <input
                        type="file"
                        name="excel_file"
                        class="form-control schedule-file-input {{ $errors->has('excel_file') ? 'is-invalid' : '' }}"
                        accept=".xlsx,.xls"
                        required>

                    @error('excel_file')
                    <div class="form-error">{{ $message }}</div>
                    @enderror

                    <div class="form-hint">
                        Format yang didukung: .xlsx dan .xls. Maksimal ukuran file 10 MB.
                    </div>
                </div>

                <div class="schedule-import-actions">
                    <button type="submit" class="btn btn-primary schedule-import-submit">
                        Preview Import
                    </button>

                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection