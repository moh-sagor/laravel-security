<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\Rules\XssRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Tests\TestCase;

class XssRuleTest extends TestCase
{
    /** @test */
    public function it_detects_script_tag_xss()
    {
        $request = Request::create('/comment', 'POST', ['body' => "<script>alert('xss')</script>"]);
        $context = new SecurityContext($request);

        $rule = new XssRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
        $this->assertEquals('xss.detector', $result->getRule());
    }

    /** @test */
    public function it_detects_inline_event_handler_xss()
    {
        $request = Request::create('/profile', 'POST', ['bio' => '<img src="x" onerror="alert(1)">']);
        $context = new SecurityContext($request);

        $rule = new XssRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
    }

    /** @test */
    public function it_does_not_flag_harmless_text_content()
    {
        $request = Request::create('/comment', 'POST', ['body' => 'Hello! This is a simple comment without HTML tags.']);
        $context = new SecurityContext($request);

        $rule = new XssRule();
        $result = $rule->check($context);

        $this->assertFalse($result->isDetected());
    }
}
