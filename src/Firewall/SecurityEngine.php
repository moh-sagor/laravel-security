<?php

namespace Sagor\LaravelSecurity\Firewall;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Events\SecurityRequestBlocked;
use Sagor\LaravelSecurity\Events\SecurityRequestThrottled;
use Sagor\LaravelSecurity\Events\SecurityThreatDetected;
use Sagor\LaravelSecurity\Support\SecurityLogger;

class SecurityEngine
{
    /**
     * @var RuleRegistry
     */
    protected $registry;

    /**
     * @var SecurityPolicy
     */
    protected $policy;

    /**
     * @var array
     */
    protected $config;

    /**
     * SecurityEngine constructor.
     *
     * @param RuleRegistry $registry
     * @param SecurityPolicy $policy
     * @param array $config
     */
    public function __construct(RuleRegistry $registry, SecurityPolicy $policy, array $config = [])
    {
        $this->registry = $registry;
        $this->policy = $policy;
        $this->config = $config;
    }

    /**
     * Inspect an incoming HTTP request and return a SecurityDecision.
     *
     * @param Request $request
     * @return SecurityDecision
     */
    public function inspect(Request $request): SecurityDecision
    {
        $failureMode = isset($this->config['failure_mode']) ? $this->config['failure_mode'] : 'fail_open';

        try {
            // Check master toggle
            $enabled = isset($this->config['enabled']) ? $this->config['enabled'] : true;
            if (!$enabled) {
                return SecurityDecision::allow(new RiskScore(0), 'Security firewall disabled.');
            }

            $context = new SecurityContext($request);
            $ip = $context->getIp();

            // 1. IP Allowlist & Blocklist checks
            $allowlist = isset($this->config['ip']['allowlist']) ? (array) $this->config['ip']['allowlist'] : [];
            $blocklist = isset($this->config['ip']['blocklist']) ? (array) $this->config['ip']['blocklist'] : [];

            $isAllowed = IpManager::isAllowed($ip, $allowlist);
            $isBlocked = IpManager::isBlocked($ip, $blocklist);

            if ($isAllowed) {
                return SecurityDecision::allow(new RiskScore(0), 'Client IP explicitly allowlisted.');
            }

            if ($isBlocked) {
                $decision = SecurityDecision::block(new RiskScore(100), 'Client IP address blocked by policy.', 403);
                $this->handleDecisionEvents($context, $decision);
                return $decision;
            }

            // 2. Execute Rule Registry Pipeline
            $results = [];
            $firewallConfig = isset($this->config['firewall']) ? (array) $this->config['firewall'] : [];

            foreach ($this->registry->getRules() as $rule) {
                if ($rule instanceof SecurityRule) {
                    $ruleId = $rule->getId();
                    // Check if specific rule type is enabled in config
                    if ($this->isRuleEnabled($ruleId, $firewallConfig)) {
                        $result = $rule->check($context);
                        $results[] = $result;
                    }
                }
            }

            // 3. Evaluate Policy & Decision
            $decision = $this->policy->evaluate($context, $results, $isBlocked, $isAllowed);

            // 4. Dispatch Events & Log Security Events
            $this->handleDecisionEvents($context, $decision);

            return $decision;

        } catch (\Throwable $e) {
            SecurityLogger::logError('SecurityEngine exception: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($failureMode === 'fail_closed') {
                return SecurityDecision::block(new RiskScore(100), 'Internal Security Engine Failure (Fail-Closed).', 500);
            }

            return SecurityDecision::allow(new RiskScore(0), 'Internal Security Engine Failure (Fail-Open).');
        }
    }

    /**
     * Determine if a rule is enabled in configuration.
     *
     * @param string $ruleId
     * @param array $config
     * @return bool
     */
    protected function isRuleEnabled(string $ruleId, array $config): bool
    {
        if (strpos($ruleId, 'sqli') !== false) {
            return isset($config['sql_injection']) ? (bool) $config['sql_injection'] : true;
        }
        if (strpos($ruleId, 'xss') !== false) {
            return isset($config['xss']) ? (bool) $config['xss'] : true;
        }
        if (strpos($ruleId, 'path_traversal') !== false) {
            return isset($config['path_traversal']) ? (bool) $config['path_traversal'] : true;
        }
        if (strpos($ruleId, 'command_injection') !== false) {
            return isset($config['command_injection']) ? (bool) $config['command_injection'] : true;
        }
        if (strpos($ruleId, 'ssrf') !== false) {
            return isset($config['ssrf']) ? (bool) $config['ssrf'] : true;
        }
        if (strpos($ruleId, 'scanner_detection') !== false) {
            return isset($config['scanner_detection']) ? (bool) $config['scanner_detection'] : true;
        }
        if (strpos($ruleId, 'suspicious_ua') !== false || strpos($ruleId, 'bot') !== false) {
            return isset($config['bot_detection']) ? (bool) $config['bot_detection'] : true;
        }
        if (strpos($ruleId, 'parameter_pollution') !== false) {
            return isset($config['parameter_pollution']) ? (bool) $config['parameter_pollution'] : true;
        }
        if (strpos($ruleId, 'encoding_attack') !== false) {
            return isset($config['encoding_attacks']) ? (bool) $config['encoding_attacks'] : true;
        }
        if (strpos($ruleId, 'request_size') !== false) {
            return isset($config['request_size']) ? (bool) $config['request_size'] : true;
        }

        return true;
    }

    /**
     * Dispatch events and log security threats.
     *
     * @param SecurityContext $context
     * @param SecurityDecision $decision
     * @return void
     */
    protected function handleDecisionEvents(SecurityContext $context, SecurityDecision $decision)
    {
        $riskScore = $decision->getRiskScore();

        if ($riskScore->getScore() > 0) {
            SecurityLogger::logThreat($context, $decision);

            try {
                if (function_exists('event')) {
                    event(new SecurityThreatDetected($context, $decision));

                    if ($decision->isBlocked()) {
                        event(new SecurityRequestBlocked($context, $decision));
                    } elseif ($decision->isThrottled()) {
                        event(new SecurityRequestThrottled($context, $decision));
                    }
                }
            } catch (\Throwable $e) {
                // Ignore event dispatch errors in legacy environments
            }
        }
    }

    /**
     * @return RuleRegistry
     */
    public function getRegistry(): RuleRegistry
    {
        return $this->registry;
    }
}
