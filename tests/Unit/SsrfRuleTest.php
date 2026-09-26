<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\Rules\SsrfRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Tests\TestCase;

class SsrfRuleTest extends TestCase
{
    /** @test */
    public function it_detects_cloud_metadata_ssrf()
    {
        $request = Request::create('/fetch', 'POST', ['url' => 'http://169.254.169.254/latest/meta-data/']);
        $context = new SecurityContext($request);

        $rule = new SsrfRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
    }

    /** @test */
    public function it_detects_loopback_ssrf()
    {
        $request = Request::create('/webhook', 'POST', ['endpoint' => 'http://127.0.0.1:8000/admin']);
        $context = new SecurityContext($request);

        $rule = new SsrfRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
    }
}
