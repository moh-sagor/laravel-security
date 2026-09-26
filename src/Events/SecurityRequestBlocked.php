<?php

namespace Sagor\LaravelSecurity\Events;

use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityDecision;

class SecurityRequestBlocked
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
     * SecurityRequestBlocked constructor.
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
