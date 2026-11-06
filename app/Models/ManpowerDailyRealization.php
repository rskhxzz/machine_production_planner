<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ManpowerDailyRealization extends Model
{
    protected $table = 'manpower_daily_realizations';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'work_date',
        'os_per_day',
        'tk_per_day',
        'magang_per_day',
        'non_shift_assistance_per_day',
        'additional_manpower_per_day',
        'is_deleted',
    ];

    protected $casts = [
        'work_date' => 'date',
        'os_per_day' => 'integer',
        'tk_per_day' => 'integer',
        'magang_per_day' => 'integer',
        'non_shift_assistance_per_day' => 'integer',
        'additional_manpower_per_day' => 'integer',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ManpowerDailyRealization $realization): void {
            if (empty($realization->id)) {
                $realization->id = (string) Str::uuid();
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

    public function getTotalRealizationAttribute(): int
    {
        return (int) (
            $this->os_per_day
            + $this->tk_per_day
            + $this->magang_per_day
            + $this->non_shift_assistance_per_day
            + $this->additional_manpower_per_day
        );
    }
}
