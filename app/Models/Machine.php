<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Machine extends Model
{
    protected $table = 'machines';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'machine_code',
        'machine_name',
        'machine_type',
        'description',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Machine $machine): void {
            if (empty($machine->id)) {
                $machine->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function productionSchedules(): HasMany
    {
        return $this->hasMany(
            ProductionSchedule::class,
            'machine_id',
            'id'
        )->where('is_deleted', false);
    }

    public function manpowerOverrides(): HasMany
    {
        return $this->hasMany(
            ManpowerMachineOverride::class,
            'machine_id',
            'id'
        )->where('is_deleted', false);
    }

    public function isAutoType(): bool
    {
        return $this->machine_type === 'AUTO';
    }

    public function isSemiType(): bool
    {
        return $this->machine_type === 'SEMI';
    }
}
