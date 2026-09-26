<?php

namespace Sagor\LaravelSecurity\Contracts;

use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

interface SecurityRule
{
    /**
     * Inspect the security context and return a structured rule result.
     *
     * @param SecurityContext $context
     * @return SecurityRuleResult
     */
    public function check(SecurityContext $context): SecurityRuleResult;

    /**
     * Unique identifier for this rule.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Human-readable description of the security rule.
     *
     * @return string
     */
    public function getDescription(): string;
}
