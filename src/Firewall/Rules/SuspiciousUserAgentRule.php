<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class SuspiciousUserAgentRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'suspicious_ua.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects missing or suspicious User-Agent headers on non-API routes.';
    }

    /**
     * @param SecurityContext $context
     * @return SecurityRuleResult
     */
    public function check(SecurityContext $context): SecurityRuleResult
    {
        $ua = $context->getUserAgent();

        // Empty User-Agent
        if (trim($ua) === '') {
            return SecurityRuleResult::threat(
                $this->getId(),
                'medium',
                0.75,
                35,
                'Request missing User-Agent header.',
                ['user_agent' => '']
            );
        }

        // Check extremely short or malformed User-Agents
        if (strlen($ua) < 5 && !in_array(strtolower($ua), ['curl', 'wget'])) {
            return SecurityRuleResult::threat(
                $this->getId(),
                'medium',
                0.80,
                40,
                'Suspiciously short User-Agent header.',
                ['user_agent' => $ua]
            );
        }

        return SecurityRuleResult::clean($this->getId());
    }
}
