<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Sagor\LaravelSecurity\Support\SecuritySanitizer;
use Sagor\LaravelSecurity\Tests\TestCase;

class SecuritySanitizerTest extends TestCase
{
    /** @test */
    public function it_redacts_sensitive_fields()
    {
        $input = [
            'username' => 'john_doe',
            'password' => 'SuperSecret123!',
            'card_number' => '4111111111111111',
            'metadata' => [
                'token' => 'bearer_abc123',
                'public_id' => '12345',
            ],
        ];

        $redacted = SecuritySanitizer::redactArray($input);

        $this->assertEquals('john_doe', $redacted['username']);
        $this->assertEquals('[REDACTED]', $redacted['password']);
        $this->assertEquals('[REDACTED]', $redacted['card_number']);
        $this->assertEquals('[REDACTED]', $redacted['metadata']['token']);
        $this->assertEquals('12345', $redacted['metadata']['public_id']);
    }
}
