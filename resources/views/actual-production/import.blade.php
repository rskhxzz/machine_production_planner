@extends('layouts.app')

@section('title', 'Import Aktual Produksi')
@section('page-title', 'Import Data MES')

@push('styles')
<style>
    .mes-import-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }

    .mes-import-form {
        max-width: 720px;
    }

    .mes-import-info {
        padding: 12px 14px;
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-radius: 7px;
        color: #166534;
        font-size: 12px;
        line-height: 1.6;
        margin-bottom: 18px;
    }

    .mes-import-warning {
        padding: 12px 14px;
        background: #FFFBEB;
        border: 1px solid #FDE68A;
        border-radius: 7px;
        color: #92400E;
        font-size: 12px;
        line-height: 1.6;
        margin-bottom: 18px;
    }

    .mes-upload-input {
        padding: 12px;
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: 7px;
        cursor: pointer;
    }

    .mes-upload-input:hover {
        border-color: var(--green);
        background: #F0FDF4;
    }

    .mes-import-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .mes-import-table {
        min-width: 650px;
    }

    .mes-import-mobile {
        display: none;
    }

    .mes-import-mobile-item {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
    }

    .mes-import-mobile-item:last-child {
        border-bottom: none;
    }

    .mes-import-mobile-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 8px;
    }

    .mes-import-file {
        font-size: 13px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .mes-import-date {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
    }

    .mes-import-mobile-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 10px;
        background: #F8FAFC;
        border-radius: 6px;
        margin-top: 10px;
    }

    .mes-import-stat-label {
        font-size: 10px;
        color: var(--text-muted);
    }

    .mes-import-stat-value {
        font-size: 13px;
        font-weight: 600;
        margin-top: 2px;
        font-variant-numeric: tabular-nums;
    }

    @media (max-width: 768px) {
        .mes-import-table-container {
            display: none;
        }

        .mes-import-mobile {
            display: block;
        }

        .mes-import-form {
            max-width: none;
        }
    }

    @media (max-width: 480px) {
        .mes-import-actions {
            flex-direction: column;
        }

        .mes-import-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="mes-import-page">

    <div class="mes-import-actions">
        <a href="{{ route('mes.index') }}" class="btn btn-secondary btn-sm">
            Kembali ke Aktual Produksi
        </a>

        <a href="{{ route('mes.history') }}" class="btn btn-secondary btn-sm">
            Riwayat Import
        </a>
    </div>

    <div class="card mes-import-form">
        <div class="card-header">
            <span class="card-title">Upload File Excel DATA MES</span>
        </div>

        <div class="card-body">

            <div class="mes-import-info">
                <strong>Informasi Import</strong><br>
                Sistem akan mendeteksi seluruh tanggal produksi dalam file.
                Data lama pada tanggal yang sama akan diarsipkan, kemudian diganti
                dengan data baru. Data pada tanggal lain tidak akan terpengaruh.
            </div>

            <div class="mes-import-warning">
                <strong>Perhatian:</strong>
                Pastikan file Excel yang diunggah sudah benar.
                Proses import akan mengarsipkan data lama pada tanggal yang terdampak.
            </div>

            <form
                method="POST"
                action="{{ route('mes.import.preview') }}"
                enctype="multipart/form-data">

                @csrf

                <div class="form-group">
                    <label class="form-label required">File Excel DATA MES</label>

                    <input
                        type="file"
                        name="excel_file"
                        class="form-control mes-upload-input {{ $errors->has('excel_file') ? 'is-invalid' : '' }}"
                        accept=".xlsx,.xls"
                        required>

                    @error('excel_file')
                    <div class="form-error">{{ $message }}</div>
                    @enderror

                    <div class="form-hint">
                        Format file: .xlsx atau .xls. Maksimal ukuran file 50 MB.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Tipe Import</label>

                    <select
                        name="import_type"
                        class="form-control {{ $errors->has('import_type') ? 'is-invalid' : '' }}"
                        required>

                        <option value="DAILY" {{ old('import_type', 'DAILY') === 'DAILY' ? 'selected' : '' }}>
                            DAILY — Data harian
                        </option>

                        <option value="CUMULATIVE" {{ old('import_type') === 'CUMULATIVE' ? 'selected' : '' }}>
                            CUMULATIVE — Data kumulatif
                        </option>
                    </select>

                    @error('import_type')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mes-import-actions">
                    <button type="submit" class="btn btn-primary">
                        Preview Import
                    </button>

                    <a href="{{ route('mes.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Import Terakhir</span>

            <a href="{{ route('mes.history') }}" class="btn btn-secondary btn-sm">
                Lihat Semua
            </a>
        </div>

        @if($batches->count() > 0)

        <div class="table-container mes-import-table-container">
            <table class="mes-import-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Tipe</th>
                        <th>Tanggal Import</th>
                        <th class="text-right">Data Diarsip</th>
                        <th class="text-right">Data Baru</th>
                        <th>Status</th>
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
                        <td
                            style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"
                            title="{{ $batch->file_name }}">
                            <span class="font-semibold">{{ $batch->file_name }}</span>
                        </td>

                        <td>
                            <span class="badge badge-blue">{{ $batch->import_type }}</span>
                        </td>

                        <td class="nowrap text-muted">
                            {{ $batch->created_at?->format('d/m/Y H:i') ?? '-' }}
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mes-import-mobile">
            @foreach($batches as $batch)
            @php
            $statusClass = match($batch->status) {
            'SUCCESS' => 'badge-green',
            'FAILED' => 'badge-red',
            default => 'badge-yellow',
            };
            @endphp

            <div class="mes-import-mobile-item">
                <div class="mes-import-mobile-top">
                    <div style="min-width:0;">
                        <div class="mes-import-file">{{ $batch->file_name }}</div>

                        <div class="mes-import-date">
                            {{ $batch->created_at?->format('d M Y, H:i') ?? '-' }}
                        </div>
                    </div>

                    <span class="badge {{ $statusClass }}">
                        {{ $batch->status }}
                    </span>
                </div>

                <div style="display:flex;gap:8px;align-items:center;">
                    <span class="badge badge-blue">{{ $batch->import_type }}</span>
                </div>

                <div class="mes-import-mobile-stats">
                    <div>
                        <div class="mes-import-stat-label">Data Diarsip</div>
                        <div class="mes-import-stat-value">
                            {{ number_format($batch->deleted_rows) }}
                        </div>
                    </div>

                    <div>
                        <div class="mes-import-stat-label">Data Baru</div>
                        <div class="mes-import-stat-value">
                            {{ number_format($batch->inserted_rows) }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>Belum ada riwayat import</h3>
            <p>Riwayat import DATA MES akan ditampilkan di sini.</p>
        </div>
        @endif
    </div>

</div>
@endsection