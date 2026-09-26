<?php

namespace Sagor\LaravelSecurity\Contracts;

use Sagor\LaravelSecurity\Bot\BotScore;
use Sagor\LaravelSecurity\Firewall\SecurityContext;

interface BotDetector
{
    /**
     * Analyze request context and evaluate bot probability/score.
     *
     * @param SecurityContext $context
     * @return BotScore
     */
    public function analyze(SecurityContext $context): BotScore;
}
