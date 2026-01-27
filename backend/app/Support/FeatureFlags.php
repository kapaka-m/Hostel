<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class FeatureFlags
{
    public static function enabled(string $flag): bool
    {
        $key = self::normalizeKey($flag);

        if (app()->runningUnitTests()) {
            return self::resolve($key);
        }

        $ttl = (int) config('feature-flags.cache_ttl', 30);

        return Cache::remember(self::cacheKey($key), $ttl, fn() => self::resolve($key));
    }

    public static function clearCache(?string $flag = null): void
    {
        if ($flag === null) {
            Cache::flush();

            return;
        }

        Cache::forget(self::cacheKey(self::normalizeKey($flag)));
    }

    protected static function resolve(string $key): bool
    {
        $defaultKey = str_starts_with($key, 'feature.') ? substr($key, 8) : $key;
        $default = (bool) config("feature-flags.defaults.$defaultKey", false);

        if (!Schema::hasTable('system_settings')) {
            return $default;
        }

        return (bool) SystemSetting::getValue($key, $default);
    }

    protected static function normalizeKey(string $flag): string
    {
        return str_starts_with($flag, 'feature.') ? $flag : 'feature.' . $flag;
    }

    protected static function cacheKey(string $key): string
    {
        return 'feature_flags:' . $key;
    }
}
