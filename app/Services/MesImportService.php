<?php

namespace App\Services;

use App\Models\MesImportBatch;
use App\Models\MesProduction;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class MesImportService
{
    private const NUMERIC_FIELDS = [
        'ordqty',
        'ordwgt',
        'bp',
        'wst_prd',
        'prd_a',
        'prd_b',
        'qtyprd_a',
        'qtyprd_b',
        'qtyprd',
        'sisiran',
        'sobekan',
        'bpusg',
        'score',
        'wgtgf',
        'sisaakhirshift',
        'rejectother',
        'wgtroll',
        'bndsiz',
        'opnsiz',
        'gsm',
        'nosheet',
        'rollwidth',
    ];

    public function parseExcel(
        string $filePath,
        string $originalName
    ): array {
        if (!is_file($filePath)) {
            return ['error' => 'File Excel tidak ditemukan.'];
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (Throwable $e) {
            return ['error' => 'File Excel tidak dapat dibaca.'];
        }

        $selectedSheet = null;
        $headerIndex = null;
        $columnMap = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $rows = $sheet->toArray(null, true, true, false);

            foreach ($rows as $index => $row) {
                $normalizedHeaders = array_map(
                    fn($value) => $this->normalizeHeader($value),
                    $row
                );

                if (
                    in_array('datprd', $normalizedHeaders, true) &&
                    in_array('mcnnam', $normalizedHeaders, true)
                ) {
                    $selectedSheet = $sheet;
                    $headerIndex = $index;
                    $columnMap = $this->buildColumnMap($normalizedHeaders);
                    break 2;
                }
            }
        }

        if ($selectedSheet === null || $headerIndex === null) {
            return [
                'error' => 'Header DATA MES tidak ditemukan. Pastikan terdapat kolom datprd dan mcnnam.',
            ];
        }

        foreach (['datprd', 'mcnnam'] as $requiredField) {
            if (!array_key_exists($requiredField, $columnMap)) {
                return [
                    'error' => "Kolom wajib {$requiredField} tidak ditemukan.",
                ];
            }
        }

        $rows = $selectedSheet->toArray(null, true, true, false);

        $records = [];
        $errors = [];
        $dateSet = [];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= $headerIndex) {
                continue;
            }

            $nonEmpty = array_filter(
                $row,
                fn($value) => $value !== null && trim((string) $value) !== ''
            );

            if (empty($nonEmpty)) {
                continue;
            }

            $excelRow = $rowIndex + 1;
            $record = $this->mapRow($row, $columnMap, $excelRow);

            if (empty($record['mcnnam'])) {
                $errors[] = "Baris {$excelRow}: Kode mesin kosong.";
                continue;
            }

            try {
                $record['datprd'] = $this->normalizeDate(
                    $record['datprd'] ?? null
                );
            } catch (Throwable $e) {
                $errors[] = "Baris {$excelRow}: Tanggal produksi tidak valid.";
                continue;
            }

            $records[] = $record;
            $dateSet[$record['datprd']] = true;
        }

        $dates = array_keys($dateSet);
        sort($dates);

        if (empty($records)) {
            return [
                'error' => 'Tidak ditemukan record DATA MES yang valid.',
                'errors' => $errors,
            ];
        }

        $existingCounts = [];

        if (!empty($dates)) {
            $existing = MesProduction::query()
                ->active()
                ->whereIn('datprd', $dates)
                ->selectRaw('datprd::text AS date_str, COUNT(*) AS total')
                ->groupBy('datprd')
                ->pluck('total', 'date_str');

            foreach ($existing as $date => $count) {
                $existingCounts[$date] = (int) $count;
            }
        }

        $datesSummary = [];

        foreach ($dates as $date) {
            $newRows = count(
                array_filter(
                    $records,
                    fn($record) => $record['datprd'] === $date
                )
            );

            $datesSummary[] = [
                'date' => $date,
                'new_rows' => $newRows,
                'existing_rows' => $existingCounts[$date] ?? 0,
            ];
        }

        return [
            'records' => $records,
            'dates' => $dates,
            'dates_summary' => $datesSummary,
            'total_rows' => count($records),
            'errors' => $errors,
            'can_import' => empty($errors),
        ];
    }

    public function executeImport(
        array $records,
        array $dates,
        string $fileName,
        string $importType
    ): array {
        if (empty($records)) {
            return [
                'success' => false,
                'error' => 'Tidak ada data untuk diimport.',
            ];
        }

        if (!in_array($importType, ['DAILY', 'CUMULATIVE'], true)) {
            return [
                'success' => false,
                'error' => 'Tipe import tidak valid.',
            ];
        }

        $dates = array_values(
            array_unique(
                array_filter(
                    array_column($records, 'datprd')
                )
            )
        );

        sort($dates);

        if (empty($dates)) {
            return [
                'success' => false,
                'error' => 'Tanggal produksi tidak ditemukan.',
            ];
        }

        foreach ($records as $record) {
            if (
                empty($record['datprd']) ||
                empty($record['mcnnam']) ||
                !in_array($record['datprd'], $dates, true)
            ) {
                return [
                    'success' => false,
                    'error' => 'Terdapat record yang tidak memenuhi validasi.',
                ];
            }
        }

        $batchId = (string) Str::uuid();
        $startedAt = now();

        try {
            $result = DB::transaction(function () use (
                $records,
                $dates,
                $fileName,
                $importType,
                $batchId,
                $startedAt
            ): array {
                $batch = MesImportBatch::create([
                    'id' => $batchId,
                    'file_name' => $fileName,
                    'import_type' => $importType,
                    'dates_in_file' => $dates,
                    'total_rows' => count($records),
                    'deleted_rows' => 0,
                    'inserted_rows' => 0,
                    'status' => MesImportBatch::STATUS_PROCESSING,
                    'started_at' => $startedAt,
                ]);

                $deletedRows = MesProduction::query()
                    ->active()
                    ->whereIn('datprd', $dates)
                    ->count();

                MesProduction::query()
                    ->active()
                    ->whereIn('datprd', $dates)
                    ->update([
                        'is_deleted' => true,
                        'updated_at' => now(),
                    ]);

                $insertedRows = 0;

                foreach (array_chunk($records, 100) as $chunk) {
                    $insertData = [];
                    $timestamp = now();

                    foreach ($chunk as $record) {
                        $record['id'] = (string) Str::uuid();
                        $record['import_batch_id'] = $batch->id;
                        $record['source_file'] = $fileName;
                        $record['is_deleted'] = false;
                        $record['created_at'] = $timestamp;
                        $record['updated_at'] = $timestamp;

                        $insertData[] = $record;
                    }

                    MesProduction::insert($insertData);

                    $insertedRows += count($insertData);
                }

                $batch->update([
                    'deleted_rows' => $deletedRows,
                    'inserted_rows' => $insertedRows,
                    'status' => MesImportBatch::STATUS_SUCCESS,
                    'completed_at' => now(),
                ]);

                return [
                    'success' => true,
                    'batch_id' => $batch->id,
                    'deleted_rows' => $deletedRows,
                    'inserted_rows' => $insertedRows,
                    'dates_count' => count($dates),
                ];
            });

            return $result;
        } catch (Throwable $e) {
            try {
                MesImportBatch::create([
                    'id' => $batchId,
                    'file_name' => $fileName,
                    'import_type' => $importType,
                    'dates_in_file' => $dates,
                    'total_rows' => count($records),
                    'deleted_rows' => 0,
                    'inserted_rows' => 0,
                    'status' => MesImportBatch::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                    'started_at' => $startedAt,
                    'completed_at' => now(),
                ]);
            } catch (Throwable $ignored) {
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function normalizeHeader(mixed $value): string
    {
        return strtolower(
            preg_replace(
                '/[^a-z0-9]/',
                '',
                trim((string) $value)
            )
        );
    }

    private function buildColumnMap(array $headers): array
    {
        $mapping = $this->getColumnMapping();
        $columnMap = [];

        foreach ($headers as $index => $header) {
            if (isset($mapping[$header])) {
                $columnMap[$mapping[$header]] = $index;
            }
        }

        return $columnMap;
    }

    private function getColumnMapping(): array
    {
        return [
            'datprd' => 'datprd',
            'shft' => 'shft',
            'regu' => 'regu',
            'mcnnam' => 'mcnnam',
            'spkmcnno' => 'spkmcnno',
            'prpno' => 'prpno',
            'rtenam' => 'rtenam',
            'ordqty' => 'ordqty',
            'ordwgt' => 'ordwgt',
            'bp' => 'bp',
            'wstprd' => 'wst_prd',
            'prda' => 'prd_a',
            'prdb' => 'prd_b',
            'qtyprda' => 'qtyprd_a',
            'qtyprdb' => 'qtyprd_b',
            'qtyprd' => 'qtyprd',
            'uom' => 'uom',
            'sisiran' => 'sisiran',
            'sobekan' => 'sobekan',
            'bpusg' => 'bpusg',
            'score' => 'score',
            'wgtgf' => 'wgtgf',
            'sisaakhirshift' => 'sisaakhirshift',
            'rejectother' => 'rejectother',
            'custnam' => 'custnam',
            'countryname' => 'country_name',
            'pcddsc' => 'pcddsc',
            'comatcls' => 'co_matcls',
            'wgtroll' => 'wgtroll',
            'bndsiz' => 'bndsiz',
            'opnsiz' => 'opnsiz',
            'gsm' => 'gsm',
            'nosheet' => 'nosheet',
            'rollwidth' => 'rollwidth',
        ];
    }

    private function mapRow(
        array $row,
        array $columnMap,
        int $excelRow
    ): array {
        $record = [
            'source_excel_row' => $excelRow,
        ];

        foreach ($columnMap as $field => $columnIndex) {
            $value = $row[$columnIndex] ?? null;

            if ($value === null || trim((string) $value) === '') {
                $record[$field] = null;
                continue;
            }

            if (in_array($field, self::NUMERIC_FIELDS, true)) {
                $record[$field] = $this->normalizeNumber($value);
                continue;
            }

            $record[$field] = trim((string) $value);
        }

        return $record;
    }

    private function normalizeNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $value = str_replace(["\xc2\xa0", ' '], '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace(',', '', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                'Format angka tidak valid: ' . $value
            );
        }

        return (float) $value;
    }

    private function normalizeDate(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            return Carbon::instance(
                ExcelDate::excelToDateTimeObject((float) $value)
            )->format('Y-m-d');
        }

        if ($value === null || trim((string) $value) === '') {
            throw new InvalidArgumentException(
                'Tanggal produksi kosong.'
            );
        }

        return Carbon::parse(trim((string) $value))
            ->format('Y-m-d');
    }
}
