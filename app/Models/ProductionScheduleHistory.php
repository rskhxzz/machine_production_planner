<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductionScheduleHistory extends Model
{
    protected $table = 'production_schedules_history';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'schedule_id',
        'change_type',
        'old_data',
        'new_data',
        'change_reason',
        'changed_at',
        'is_deleted',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'changed_at' => 'datetime',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductionScheduleHistory $history): void {
            if (empty($history->id)) {
                $history->id = (string) Str::uuid();
            }

            if (empty($history->changed_at)) {
                $history->changed_at = now();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(
            ProductionSchedule::class,
            'schedule_id',
            'id'
        );
    }

    public function getChangeTypeLabelAttribute(): string
    {
        return match ($this->change_type) {
            'CREATE' => 'Jadwal Dibuat',
            'UPDATE' => 'Jadwal Diperbarui',
            'SHIFT' => 'Jadwal Digeser',
            default => 'Perubahan Jadwal',
        };
    }
}
