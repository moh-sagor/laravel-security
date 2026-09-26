<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\Rules\SqlInjectionRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Tests\TestCase;

class SqlInjectionRuleTest extends TestCase
{
    /** @test */
    public function it_detects_union_select_sql_injection()
    {
        $request = Request::create('/search', 'GET', ['q' => 'UNION SELECT 1,2,3']);
        $context = new SecurityContext($request);

        $rule = new SqlInjectionRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
        $this->assertEquals('sqli.detector', $result->getRule());
        $this->assertGreaterThanOrEqual(80, $result->getScore());
    }

    /** @test */
    public function it_detects_boolean_tautology_sqli()
    {
        $request = Request::create('/login', 'POST', ['username' => "' OR '1'='1"]);
        $context = new SecurityContext($request);

        $rule = new SqlInjectionRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
    }

    /** @test */
    public function it_does_not_flag_legitimate_search_queries()
    {
        $request = Request::create('/search', 'GET', ['q' => 'I want to select a nice blue shirt for my wedding']);
        $context = new SecurityContext($request);

        $rule = new SqlInjectionRule();
        $result = $rule->check($context);

        $this->assertFalse($result->isDetected());
    }
}
