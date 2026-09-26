<?php

namespace Sagor\LaravelSecurity\RateLimit;

use Illuminate\Support\Facades\Cache;

class RateLimiter
{
    /**
     * Determine if key has exceeded allowed rate limit using sliding window algorithm.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return bool True if allowed, false if limit exceeded
     */
    public function attempt(string $key, int $maxAttempts = 120, int $decaySeconds = 60): bool
    {
        $cacheKey = 'security:ratelimit:' . md5($key);
        $currentTime = time();
        $windowStart = $currentTime - $decaySeconds;

        try {
            $requests = (array) Cache::get($cacheKey, []);
            // Filter out timestamps outside window
            $validRequests = [];
            foreach ($requests as $ts) {
                if ($ts > $windowStart) {
                    $validRequests[] = (int) $ts;
                }
            }

            if (count($validRequests) >= $maxAttempts) {
                return false; // Limit exceeded
            }

            $validRequests[] = $currentTime;
            Cache::put($cacheKey, $validRequests, $decaySeconds);

            return true;
        } catch (\Throwable $e) {
            // Fail open if cache fails
            return true;
        }
    }

    /**
     * Get remaining available attempts for given key.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return int
     */
    public function remaining(string $key, int $maxAttempts = 120, int $decaySeconds = 60): int
    {
        $cacheKey = 'security:ratelimit:' . md5($key);
        $currentTime = time();
        $windowStart = $currentTime - $decaySeconds;

        try {
            $requests = (array) Cache::get($cacheKey, []);
            $count = 0;
            foreach ($requests as $ts) {
                if ($ts > $windowStart) {
                    $count++;
                }
            }

            return max(0, $maxAttempts - $count);
        } catch (\Throwable $e) {
            return $maxAttempts;
        }
    }

    /**
     * Reset rate limit counter for given key.
     *
     * @param string $key
     * @return void
     */
    public function clear(string $key)
    {
        $cacheKey = 'security:ratelimit:' . md5($key);
        try {
            Cache::forget($cacheKey);
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}
