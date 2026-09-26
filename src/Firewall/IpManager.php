<?php

namespace Sagor\LaravelSecurity\Firewall;

use Illuminate\Support\Facades\Cache;

class IpManager
{
    /**
     * Check if client IP is explicitly allowlisted.
     *
     * @param string $ip
     * @param array $allowlist
     * @return bool
     */
    public static function isAllowed(string $ip, array $allowlist = []): bool
    {
        foreach ($allowlist as $range) {
            if (static::matchIp($ip, $range)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if client IP is blocked (config blocklist, cache temporary block, or DB record).
     *
     * @param string $ip
     * @param array $blocklist
     * @return bool
     */
    public static function isBlocked(string $ip, array $blocklist = []): bool
    {
        // 1. Static config blocklist check
        foreach ($blocklist as $range) {
            if (static::matchIp($ip, $range)) {
                return true;
            }
        }

        // 2. Dynamic cache key block check
        $cacheKey = 'security:blocked_ip:' . md5($ip);
        if (Cache::has($cacheKey)) {
            return true;
        }

        // 3. Database model lookup if available
        try {
            if (class_exists('Sagor\LaravelSecurity\Models\BlockedIp')) {
                $hash = md5($ip);
                $blockedModel = \Sagor\LaravelSecurity\Models\BlockedIp::where('ip_hash', $hash)->first();

                if ($blockedModel) {
                    if ($blockedModel->is_permanent) {
                        return true;
                    }
                    if ($blockedModel->expires_at && strtotime((string) $blockedModel->expires_at) > time()) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fail open on database error
        }

        return false;
    }

    /**
     * Temporarily block an IP address for specified seconds.
     *
     * @param string $ip
     * @param int $durationSeconds
     * @param string $reason
     * @return bool
     */
    public static function blockTemporarily(string $ip, int $durationSeconds = 3600, string $reason = 'Temporary security block'): bool
    {
        $cacheKey = 'security:blocked_ip:' . md5($ip);
        Cache::put($cacheKey, [
            'ip' => $ip,
            'reason' => $reason,
            'blocked_at' => time(),
            'expires_at' => time() + $durationSeconds,
        ], $durationSeconds);

        return true;
    }

    /**
     * Match IP against single IP or CIDR range (IPv4 & IPv6).
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    public static function matchIp(string $ip, string $range): bool
    {
        $ip = trim($ip);
        $range = trim($range);

        if ($ip === $range) {
            return true;
        }

        if (strpos($range, '/') === false) {
            return false;
        }

        list($subnet, $bits) = explode('/', $range, 2);
        $bits = (int) $bits;

        // IPv4 CIDR Check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $mask = -1 << (32 - $bits);

            return ($ipLong & $mask) === ($subnetLong & $mask);
        }

        // IPv6 CIDR Check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);

            if ($ipBin === false || $subnetBin === false) {
                return false;
            }

            $bytes = (int) floor($bits / 8);
            $remainderBits = $bits % 8;

            if (substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
                return false;
            }

            if ($remainderBits > 0) {
                $mask = 0xFF << (8 - $remainderBits);
                return (ord($ipBin[$bytes]) & $mask) === (ord($subnetBin[$bytes]) & $mask);
            }

            return true;
        }

        return false;
    }
}
