<?php

namespace Sagor\LaravelSecurity\Bot;

use Illuminate\Support\Facades\Cache;

class BehaviorAnalyzer
{
    /**
     * Track 404 Not Found error for IP address.
     *
     * @param string $ip
     * @return int
     */
    public static function record404(string $ip): int
    {
        $cacheKey = 'security:behavior:404:' . md5($ip);
        $count = (int) Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $count, 300); // 5 min window

        return $count;
    }

    /**
     * Get 404 error frequency count for IP.
     *
     * @param string $ip
     * @return int
     */
    public static function get404Count(string $ip): int
    {
        $cacheKey = 'security:behavior:404:' . md5($ip);
        return (int) Cache::get($cacheKey, 0);
    }
}
