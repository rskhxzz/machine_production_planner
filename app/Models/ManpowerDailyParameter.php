<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ManpowerDailyParameter extends Model
{
    protected $table = 'manpower_daily_parameters';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'work_date',
        'tk_per_shift',
        'magang_per_shift',
        'non_shift_assistance_per_day',
        'additional_manpower_per_day',
        'is_deleted',
    ];

    protected $casts = [
        'work_date' => 'date',
        'tk_per_shift' => 'integer',
        'magang_per_shift' => 'integer',
        'non_shift_assistance_per_day' => 'integer',
        'additional_manpower_per_day' => 'integer',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ManpowerDailyParameter $parameter): void {
            if (empty($parameter->id)) {
                $parameter->id = (string) Str::uuid();
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

    public function scopeBetweenDates(
        Builder $query,
        string $startDate,
        string $endDate
    ): Builder {
        return $query
            ->whereDate('work_date', '>=', $startDate)
            ->whereDate('work_date', '<=', $endDate);
    }

    public function getTotalAvailableManpowerAttribute(): int
    {
        return (int) (
            ($this->tk_per_shift * 3)
            + ($this->magang_per_shift * 2)
        );
    }
}
