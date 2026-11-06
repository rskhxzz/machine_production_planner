<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MesProductionStaging extends Model
{
    protected $table = 'mes_productions_staging';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'import_batch_id',
        'source_excel_row',
        'datprd',
        'shft',
        'regu',
        'mcnnam',
        'spkmcnno',
        'prpno',
        'rtenam',
        'ordqty',
        'ordwgt',
        'bp',
        'wst_prd',
        'prd_a',
        'prd_b',
        'qtyprd_a',
        'qtyprd_b',
        'qtyprd',
        'uom',
        'sisiran',
        'sobekan',
        'bpusg',
        'score',
        'wgtgf',
        'sisaakhirshift',
        'rejectother',
        'custnam',
        'country_name',
        'pcddsc',
        'co_matcls',
        'wgtroll',
        'bndsiz',
        'opnsiz',
        'gsm',
        'nosheet',
        'rollwidth',
        'row_status',
        'validation_error',
        'is_deleted',
    ];

    protected $casts = [
        'datprd' => 'date',
        'ordqty' => 'decimal:6',
        'ordwgt' => 'decimal:6',
        'bp' => 'decimal:6',
        'wst_prd' => 'decimal:6',
        'prd_a' => 'decimal:6',
        'prd_b' => 'decimal:6',
        'qtyprd_a' => 'decimal:6',
        'qtyprd_b' => 'decimal:6',
        'qtyprd' => 'decimal:6',
        'sisiran' => 'decimal:6',
        'sobekan' => 'decimal:6',
        'bpusg' => 'decimal:6',
        'score' => 'decimal:6',
        'wgtgf' => 'decimal:6',
        'sisaakhirshift' => 'decimal:6',
        'rejectother' => 'decimal:6',
        'wgtroll' => 'decimal:6',
        'bndsiz' => 'decimal:6',
        'opnsiz' => 'decimal:6',
        'gsm' => 'decimal:6',
        'nosheet' => 'decimal:6',
        'rollwidth' => 'decimal:6',
        'source_excel_row' => 'integer',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (MesProductionStaging $staging): void {
            if (empty($staging->id)) {
                $staging->id = (string) Str::uuid();
            }

            if (empty($staging->row_status)) {
                $staging->row_status = 'PENDING';
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('row_status', 'PENDING');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('row_status', 'VALID');
    }

    public function scopeInvalid(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('row_status', 'INVALID');
    }

    public function scopeForBatch(
        Builder $query,
        string $batchId
    ): Builder {
        return $query
            ->active()
            ->where('import_batch_id', $batchId);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(
            MesImportBatch::class,
            'import_batch_id',
            'id'
        );
    }

    public function isValid(): bool
    {
        return $this->row_status === 'VALID';
    }

    public function isInvalid(): bool
    {
        return $this->row_status === 'INVALID';
    }
}
