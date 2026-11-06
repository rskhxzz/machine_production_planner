<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkingWeekday extends Model
{
    protected $table = 'working_weekdays';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'day_number',
        'day_name',
        'is_working_day',
        'is_deleted',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'is_working_day' => 'boolean',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkingWeekday $weekday): void {
            if (empty($weekday->id)) {
                $weekday->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeWorking(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('is_working_day', true);
    }

    public function scopeForDay(
        Builder $query,
        int $dayNumber
    ): Builder {
        return $query
            ->active()
            ->where('day_number', $dayNumber);
    }

    public function isWorkingDay(): bool
    {
        return $this->is_working_day && !$this->is_deleted;
    }

    public function getDayNumberAttribute($value): int
    {
        return (int) $value;
    }
}
