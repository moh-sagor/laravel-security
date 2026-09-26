<?php

namespace Sagor\LaravelSecurity\Firewall;

class SecurityPolicy
{
    /**
     * @var string
     */
    protected $mode;

    /**
     * @var array
     */
    protected $thresholds;

    /**
     * SecurityPolicy constructor.
     *
     * @param string $mode
     * @param array $thresholds
     */
    public function __construct(string $mode = 'balanced', array $thresholds = [])
    {
        $this->mode = $mode;
        $this->thresholds = array_merge([
            'low' => 30,
            'medium' => 60,
            'high' => 80,
        ], $thresholds);
    }

    /**
     * Evaluate rule results and context against policy rules to reach a decision.
     *
     * @param SecurityContext $context
     * @param SecurityRuleResult[] $results
     * @param bool $isIpBlocked
     * @param bool $isIpAllowed
     * @return SecurityDecision
     */
    public function evaluate(
        SecurityContext $context,
        array $results,
        bool $isIpBlocked = false,
        bool $isIpAllowed = false
    ): SecurityDecision {
        // If IP is explicitly allowlisted, immediately allow
        if ($isIpAllowed) {
            return SecurityDecision::allow(new RiskScore(0), 'IP address explicitly allowlisted.');
        }

        // If IP is explicitly blocklisted, block immediately
        if ($isIpBlocked) {
            return SecurityDecision::block(
                new RiskScore(100),
                'IP address explicitly blocked.',
                403
            );
        }

        $riskScore = RiskScore::fromRuleResults($results);
        $score = $riskScore->getScore();

        // In monitor mode, log/monitor everything without blocking
        if ($this->mode === 'monitor') {
            if ($score > 0) {
                return SecurityDecision::monitor($riskScore, 'Security threat detected in monitor mode.');
            }
            return SecurityDecision::allow($riskScore);
        }

        // Strict mode: lower thresholds for action
        if ($this->mode === 'strict') {
            if ($score >= 50) {
                return SecurityDecision::block($riskScore, 'Request blocked by strict security policy.', 403);
            }
            if ($score >= 20) {
                return SecurityDecision::throttle($riskScore, 'Request throttled by strict security policy.', 429);
            }
            return SecurityDecision::allow($riskScore);
        }

        // Balanced mode (default)
        if ($score >= 80) {
            return SecurityDecision::block($riskScore, 'Request blocked due to high threat score.', 403);
        }

        if ($score >= 50) {
            return SecurityDecision::throttle($riskScore, 'Request throttled due to elevated threat score.', 429);
        }

        if ($score > 0) {
            return SecurityDecision::monitor($riskScore, 'Minor risk detected.');
        }

        return SecurityDecision::allow($riskScore);
    }
}
