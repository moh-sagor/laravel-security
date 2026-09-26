<?php

namespace Sagor\LaravelSecurity\RateLimit;

class RateLimitPolicy
{
    /**
     * @var int
     */
    protected $maxRequests;

    /**
     * @var int
     */
    protected $decaySeconds;

    /**
     * RateLimitPolicy constructor.
     *
     * @param int $maxRequests
     * @param int $decaySeconds
     */
    public function __construct(int $maxRequests = 120, int $decaySeconds = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->decaySeconds = $decaySeconds;
    }

    public function getMaxRequests(): int
    {
        return $this->maxRequests;
    }

    public function getDecaySeconds(): int
    {
        return $this->decaySeconds;
    }
}
