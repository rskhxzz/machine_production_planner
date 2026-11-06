<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlannerSetting extends Model
{
    protected $table = 'planner_settings';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
        'is_deleted',
    ];

    protected $casts = [
        'setting_value' => 'json',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PlannerSetting $setting): void {
            if (empty($setting->id)) {
                $setting->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeForKey(
        Builder $query,
        string $key
    ): Builder {
        return $query->where('setting_key', $key);
    }

    public static function valueFor(
        string $key,
        mixed $default = null
    ): mixed {
        $setting = static::query()
            ->active()
            ->forKey($key)
            ->first();

        return $setting?->setting_value ?? $default;
    }

    public static function setValue(
        string $key,
        mixed $value,
        ?string $description = null
    ): self {
        $setting = static::query()
            ->active()
            ->forKey($key)
            ->first();

        if (!$setting) {
            $setting = new static();
            $setting->setting_key = $key;
        }

        $setting->setting_value = $value;

        if ($description !== null) {
            $setting->description = $description;
        }

        $setting->save();

        return $setting;
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $value = static::query()
            ->where('setting_key', $key)
            ->value('setting_value');

        return $value ?? $default;
    }
}
