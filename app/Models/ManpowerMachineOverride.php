<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ManpowerMachineOverride extends Model
{
    protected $table = 'manpower_machine_overrides';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'work_date',
        'machine_id',
        'mp_override',
        'reason',
        'is_deleted',
    ];

    protected $casts = [
        'work_date' => 'date',
        'mp_override' => 'integer',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ManpowerMachineOverride $override): void {
            if (empty($override->id)) {
                $override->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('work_date', $date);
    }

    public function scopeForMachine(
        Builder $query,
        string $machineId
    ): Builder {
        return $query->where('machine_id', $machineId);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(
            Machine::class,
            'machine_id',
            'id'
        );
    }

    public function isForDate(string $date): bool
    {
        return $this->work_date?->format('Y-m-d') === $date;
    }
}
