<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Bot\BotDetector;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Tests\TestCase;

class BotDetectorTest extends TestCase
{
    /** @test */
    public function it_identifies_known_security_scanners()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'sqlmap/1.4.7#stable']);
        $context = new SecurityContext($request);

        $detector = new BotDetector();
        $score = $detector->analyze($context);

        $this->assertTrue($score->isBot());
        $this->assertTrue($score->isMaliciousBot());
        $this->assertEquals('sqlmap', $score->getBotName());
    }

    /** @test */
    public function it_identifies_trusted_search_crawlers()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)']);
        $context = new SecurityContext($request);

        $detector = new BotDetector();
        $score = $detector->analyze($context);

        $this->assertTrue($score->isBot());
        $this->assertTrue($score->isTrusted());
    }
}
