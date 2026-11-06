<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductionSchedule extends Model
{
    protected $table = 'production_schedules';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'machine_id',
        'schedule_sequence',
        'note',
        'order_no',
        'start_date',
        'end_date',
        'mt',
        'capacity',
        'qty_pcs',
        'mp_ideal_per_shift',
        'source_file',
        'source_excel_row',
        'is_special',
        'special_shifts',
        'is_deleted',
    ];

    protected $casts = [
        'schedule_sequence' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'mt' => 'decimal:3',
        'capacity' => 'decimal:3',
        'qty_pcs' => 'decimal:6',
        'mp_ideal_per_shift' => 'decimal:2',
        'source_excel_row' => 'integer',
        'is_special' => 'boolean',
        'special_shifts' => 'array',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductionSchedule $schedule): void {
            if (empty($schedule->id)) {
                $schedule->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeRunningOn(Builder $query, string $date): Builder
    {
        return $query
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function scopeProducingOn(Builder $query, string $date): Builder
    {
        return $query
            ->active()
            ->runningOn($date)
            ->whereRaw('UPPER(TRIM(COALESCE(order_no, \'\'))) <> ?', ['STOP']);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(
            Machine::class,
            'machine_id',
            'id'
        );
    }

    public function history(): HasMany
    {
        return $this->hasMany(
            ProductionScheduleHistory::class,
            'schedule_id',
            'id'
        )->where('is_deleted', false);
    }

    public function allHistory(): HasMany
    {
        return $this->hasMany(
            ProductionScheduleHistory::class,
            'schedule_id',
            'id'
        );
    }

    public function isStopOrder(): bool
    {
        return strtoupper(trim((string) $this->order_no)) === 'STOP';
    }

    public function isRunningOn(string $date): bool
    {
        return $this->start_date !== null
            && $this->end_date !== null
            && $this->start_date->format('Y-m-d') <= $date
            && $this->end_date->format('Y-m-d') >= $date;
    }

    public function getDurationInDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        return (int) $this->start_date
            ->diffInDays($this->end_date) + 1;
    }
}
