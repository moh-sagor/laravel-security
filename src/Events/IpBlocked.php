<?php

namespace Sagor\LaravelSecurity\Events;

class IpBlocked
{
    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $reason;

    /**
     * @var int
     */
    public $duration;

    /**
     * IpBlocked constructor.
     *
     * @param string $ip
     * @param string $reason
     * @param int $duration
     */
    public function __construct(string $ip, string $reason, int $duration = 3600)
    {
        $this->ip = $ip;
        $this->reason = $reason;
        $this->duration = $duration;
    }
}
