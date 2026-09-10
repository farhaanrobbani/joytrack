<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        try {
            return Cache::remember("site_setting:$key", 3600, function () use ($key, $default) {
                $record = static::where('key', $key)->first();
                return $record ? $record->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("site_setting:$key");
    }

    public static function url(string $key, ?string $default = null): ?string
    {
        $value = static::get($key);
        if (! $value) return $default;
        if (str_starts_with($value, 'http')) return $value;
        // if stored as storage path, return public URL
        if (Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }
        return $value;
    }
}
