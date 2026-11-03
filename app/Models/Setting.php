<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (! self::tableReady()) {
            return $default;
        }

        return Cache::remember(self::cacheKey($key), now()->addMinutes(60), function () use ($key, $default) {
            $value = static::query()->where('key', $key)->value('value');

            return $value ?? $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        if (! self::tableReady()) {
            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        Cache::forget(self::cacheKey($key));
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    public static function getMany(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }

    protected static function tableReady(): bool
    {
        try {
            return Schema::hasTable((new static)->getTable());
        } catch (\Throwable) {
            return false;
        }
    }

    protected static function cacheKey(string $key): string
    {
        return 'setting.'.$key;
    }
}
