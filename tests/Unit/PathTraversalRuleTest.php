<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\Rules\PathTraversalRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Tests\TestCase;

class PathTraversalRuleTest extends TestCase
{
    /** @test */
    public function it_detects_dot_dot_slash_traversal()
    {
        $request = Request::create('/download', 'GET', ['file' => '../../../../etc/passwd']);
        $context = new SecurityContext($request);

        $rule = new PathTraversalRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
    }

    /** @test */
    public function it_detects_system_file_targets()
    {
        $request = Request::create('/view', 'GET', ['doc' => 'boot.ini']);
        $context = new SecurityContext($request);

        $rule = new PathTraversalRule();
        $result = $rule->check($context);

        $this->assertTrue($result->isDetected());
    }
}
