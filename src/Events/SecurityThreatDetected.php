<?php

namespace Sagor\LaravelSecurity\Events;

use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityDecision;

class SecurityThreatDetected
{
    /**
     * @var SecurityContext
     */
    public $context;

    /**
     * @var SecurityDecision
     */
    public $decision;

    /**
     * SecurityThreatDetected constructor.
     *
     * @param SecurityContext $context
     * @param SecurityDecision $decision
     */
    public function __construct(SecurityContext $context, SecurityDecision $decision)
    {
        $this->context = $context;
        $this->decision = $decision;
    }
}
