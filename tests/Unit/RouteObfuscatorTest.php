<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Sagor\LaravelSecurity\Route\RouteObfuscator;
use Sagor\LaravelSecurity\Tests\TestCase;

class RouteObfuscatorTest extends TestCase
{
    /** @test */
    public function it_obfuscates_sensitive_routes()
    {
        $obfuscator = new RouteObfuscator();
        $alias = $obfuscator->obfuscate('admin.users');

        $this->assertNotEquals('admin.users', $alias);
        $this->assertStringStartsWith('r/', $alias);

        $resolved = $obfuscator->resolve($alias);
        $this->assertEquals('admin.users', $resolved);
    }

    /** @test */
    public function it_respects_excluded_routes()
    {
        $obfuscator = new RouteObfuscator();
        $this->assertTrue($obfuscator->isExcluded('login'));
        $this->assertTrue($obfuscator->isExcluded('api.users'));
    }
}
