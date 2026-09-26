<?php

namespace Sagor\LaravelSecurity\Tests\Feature;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\SecurityEngine;
use Sagor\LaravelSecurity\Http\Middleware\SecurityMiddleware;
use Sagor\LaravelSecurity\Tests\TestCase;

class FirewallIntegrationTest extends TestCase
{
    /** @test */
    public function it_allows_legitimate_http_requests()
    {
        $middleware = app(SecurityMiddleware::class);
        $request = Request::create('/dashboard', 'GET');

        $response = $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_blocks_malicious_sqli_requests_with_403()
    {
        $middleware = app(SecurityMiddleware::class);
        $request = Request::create('/search?q=UNION+SELECT+1,user()', 'GET');

        $response = $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals(403, $response->getStatusCode());
    }
}
