<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Sagor\LaravelSecurity\RateLimit\RateLimiter;
use Sagor\LaravelSecurity\Tests\TestCase;

class RateLimiterTest extends TestCase
{
    /** @test */
    public function it_enforces_rate_limits()
    {
        $limiter = new RateLimiter();
        $key = 'test_ip_127.0.0.1';

        $this->assertTrue($limiter->attempt($key, 2, 60));
        $this->assertTrue($limiter->attempt($key, 2, 60));
        $this->assertFalse($limiter->attempt($key, 2, 60)); // 3rd attempt exceeds max 2
    }
}
