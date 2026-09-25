<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label', 'updated_by_id'];

    /** In-request read cache (cleared on every write). */
    protected static array $cache = [];

    /**
     * Read a setting with in-request caching. Falls back to $default when missing.
     *
     * Only real database rows are cached. Misses fall through every time so a
     * fallback default can never go stale across tests, queue workers, or
     * long-running processes that swap databases/connections.
     */
    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        try {
            $row = static::where('key', $key)->first();
        } catch (\Throwable $e) {
            return $default; // settings table not migrated yet
        }

        if (!$row) {
            return $default;
        }

        return static::$cache[$key] = $row->value;
    }

    /**
     * Clear the in-request read cache. Call after writes that bypass set(),
     * in test teardown, or when recycling long-running workers.
     */
    public static function flushCache(?string $key = null): void
    {
        if ($key === null) {
            static::$cache = [];
            return;
        }
        unset(static::$cache[$key]);
    }

    public static function getFloat(string $key, float $default): float
    {
        return (float) static::get($key, $default);
    }

    public static function getInt(string $key, int $default): int
    {
        return (int) static::get($key, $default);
    }

    public static function set(string $key, $value, ?int $updatedById = null): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value, 'updated_by_id' => $updatedById]);
        unset(static::$cache[$key]);
        Cache::forget('settings.' . $key);
    }

    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
        unset(static::$cache[$key]);
        Cache::forget('settings.' . $key);
    }
}
