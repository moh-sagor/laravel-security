<?php

namespace Sagor\LaravelSecurity\RateLimit;

use Illuminate\Support\Facades\Cache;
use Sagor\LaravelSecurity\Firewall\IpManager;

class ThreatTracker
{
    /**
     * Record a security failure or authentication error for given key/IP.
     *
     * @param string $ip
     * @param string $type
     * @param int $decaySeconds
     * @return int Current failure count
     */
    public static function recordFailure(string $ip, string $type = 'auth', int $decaySeconds = 900): int
    {
        $cacheKey = 'security:threat_track:' . md5($ip . ':' . $type);
        $count = (int) Cache::get($cacheKey, 0) + 1;

        Cache::put($cacheKey, $count, $decaySeconds);

        // Tiered enforcement
        if ($count >= 50) {
            IpManager::blockTemporarily($ip, 3600, 'Repeated threat/authentication failure threshold (50+)');
        }

        return $count;
    }

    /**
     * Get current failure count for IP.
     *
     * @param string $ip
     * @param string $type
     * @return int
     */
    public static function getFailures(string $ip, string $type = 'auth'): int
    {
        $cacheKey = 'security:threat_track:' . md5($ip . ':' . $type);
        return (int) Cache::get($cacheKey, 0);
    }
}
