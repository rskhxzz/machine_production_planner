<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MesImportBatch extends Model
{
    public const STATUS_PROCESSING = 'PROCESSING';

    public const STATUS_SUCCESS = 'SUCCESS';

    public const STATUS_FAILED = 'FAILED';

    public const TYPE_DAILY = 'DAILY';

    public const TYPE_CUMULATIVE = 'CUMULATIVE';

    protected $table = 'mes_import_batches';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'file_name',
        'import_type',
        'dates_in_file',
        'total_rows',
        'deleted_rows',
        'inserted_rows',
        'status',
        'error_message',
        'started_at',
        'completed_at',
        'is_deleted',
    ];

    protected $casts = [
        'dates_in_file' => 'array',
        'total_rows' => 'integer',
        'deleted_rows' => 'integer',
        'inserted_rows' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (MesImportBatch $batch): void {
            if (empty($batch->id)) {
                $batch->id = (string) Str::uuid();
            }

            if (empty($batch->started_at)) {
                $batch->started_at = now();
            }

            if (empty($batch->status)) {
                $batch->status = self::STATUS_PROCESSING;
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('status', self::STATUS_SUCCESS);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('status', self::STATUS_FAILED);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('status', self::STATUS_PROCESSING);
    }

    public function scopeForFile(
        Builder $query,
        string $fileName
    ): Builder {
        return $query
            ->active()
            ->where('file_name', $fileName);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(
            MesProduction::class,
            'import_batch_id',
            'id'
        )->where('is_deleted', false);
    }

    public function allProductions(): HasMany
    {
        return $this->hasMany(
            MesProduction::class,
            'import_batch_id',
            'id'
        );
    }

    public function getDateCountAttribute(): int
    {
        return count($this->dates_in_file ?? []);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }
}
