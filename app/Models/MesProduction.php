<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MesProduction extends Model
{
    protected $table = 'mes_productions';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
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
        'source_excel_row',
        'import_batch_id',
        'source_file',
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
        static::creating(function (MesProduction $production): void {
            if (empty($production->id)) {
                $production->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeForPeriod(
        Builder $query,
        string $startDate,
        string $endDate
    ): Builder {
        return $query
            ->whereDate('datprd', '>=', $startDate)
            ->whereDate('datprd', '<=', $endDate);
    }

    public function scopeForMachine(
        Builder $query,
        string $machineCode
    ): Builder {
        return $query->where('mcnnam', $machineCode);
    }

    public function scopeForShift(
        Builder $query,
        string $shift
    ): Builder {
        return $query->where('shft', $shift);
    }

    public function scopeForOrder(
        Builder $query,
        string $orderNumber
    ): Builder {
        return $query->where('prpno', 'LIKE', '%' . $orderNumber . '%');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(
            MesImportBatch::class,
            'import_batch_id',
            'id'
        );
    }

    public function getOutputKgAttribute(): float
    {
        return (float) ($this->prd_a ?? 0)
            + (float) ($this->prd_b ?? 0);
    }

    public function getOutputTonAttribute(): float
    {
        return $this->output_kg / 1000;
    }

    public function getWasteTotalAttribute(): float
    {
        return (float) ($this->wst_prd ?? 0)
            + (float) ($this->sisiran ?? 0)
            + (float) ($this->sobekan ?? 0)
            + (float) ($this->score ?? 0)
            + (float) ($this->wgtgf ?? 0);
    }

    public function getWastePercentAttribute(): float
    {
        $bp = (float) ($this->bp ?? 0);

        if ($bp <= 0) {
            return 0.0;
        }

        return ($this->waste_total / $bp) * 100;
    }

    public function getOrderNumberAttribute(): ?string
    {
        return $this->prpno;
    }

    public function getMachineCodeAttribute(): ?string
    {
        return $this->mcnnam;
    }
}
