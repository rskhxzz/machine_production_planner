<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PublicHoliday extends Model
{
    protected $table = 'public_holidays';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'holiday_date',
        'holiday_name',
        'description',
        'is_deleted',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PublicHoliday $holiday): void {
            if (empty($holiday->id)) {
                $holiday->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeOnDate(
        Builder $query,
        string $date
    ): Builder {
        return $query->whereDate('holiday_date', $date);
    }

    public function scopeBetweenDates(
        Builder $query,
        string $startDate,
        string $endDate
    ): Builder {
        return $query
            ->whereDate('holiday_date', '>=', $startDate)
            ->whereDate('holiday_date', '<=', $endDate);
    }

    public function isOnDate(string $date): bool
    {
        return $this->holiday_date?->format('Y-m-d') === $date;
    }

    public function getHolidayDateFormattedAttribute(): ?string
    {
        return $this->holiday_date?->translatedFormat('d F Y');
    }
}
