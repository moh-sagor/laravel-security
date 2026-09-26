<?php

namespace Sagor\LaravelSecurity\Events;

class IpUnblocked
{
    /**
     * @var string
     */
    public $ip;

    /**
     * IpUnblocked constructor.
     *
     * @param string $ip
     */
    public function __construct(string $ip)
    {
        $this->ip = $ip;
    }
}
