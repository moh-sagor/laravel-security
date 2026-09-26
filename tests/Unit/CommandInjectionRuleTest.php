<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\Rules\CommandInjectionRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Tests\TestCase;

class CommandInjectionRuleTest extends TestCase
{
    /** @test */
    public function it_detects_chained_command_execution()
    {
        $request = Request::create('/ping', 'POST', ['host' => '127.0.0.1; cat /etc/passwd']);
        $context = new SecurityContext($request);

        $rule = new CommandInjectionRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
    }

    /** @test */
    public function it_does_not_flag_legitimate_word_usage()
    {
        $request = Request::create('/search', 'GET', ['q' => 'cute cat pictures']);
        $context = new SecurityContext($request);

        $rule = new CommandInjectionRule();
        $result = $rule->check($context);

        $this->assertFalse($result->isDetected());
    }
}
