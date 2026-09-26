<?php

namespace Sagor\LaravelSecurity\Contracts;

interface ThreatIntelligence
{
    /**
     * Check reputation or threat score of given IP address.
     *
     * @param string $ip
     * @return array
     */
    public function checkIp(string $ip): array;
}
