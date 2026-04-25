<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public const CACHE_PREFIX = 'setting:';
    public const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value, auto-cast based on its `type`.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        $row = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key) {
            $setting = static::query()->where('key', $key)->first();

            return $setting ? ['value' => $setting->value, 'type' => $setting->type] : null;
        });

        if ($row === null) {
            return $default;
        }

        return self::castValue($row['value'], $row['type']);
    }

    /**
     * Insert or update a setting value. Bypass casting; serialize JSON if needed.
     */
    public static function put(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        $stored = self::serializeValue($value, $type);

        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget(self::CACHE_PREFIX . $key);

        return $setting;
    }

    /**
     * Forget cache for a key (or all settings if null).
     */
    public static function forget(?string $key = null): void
    {
        if ($key === null) {
            // No tag-based cache support guaranteed; consumer should iterate keys when needed.
            Cache::flush();
            return;
        }

        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Cast a stored string value back to its declared type.
     */
    protected static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'decimal' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Serialize a typed value to its stored string form.
     */
    protected static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE),
            'bool' => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
