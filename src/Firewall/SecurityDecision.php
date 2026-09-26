<?php

namespace Sagor\LaravelSecurity\Firewall;

class SecurityDecision
{
    const ACTION_ALLOW = 'allow';
    const ACTION_LOG = 'log';
    const ACTION_MONITOR = 'monitor';
    const ACTION_THROTTLE = 'throttle';
    const ACTION_CHALLENGE = 'challenge';
    const ACTION_QUARANTINE = 'quarantine';
    const ACTION_BLOCK = 'block';

    /**
     * @var string
     */
    protected $action;

    /**
     * @var RiskScore
     */
    protected $riskScore;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * SecurityDecision constructor.
     *
     * @param string $action
     * @param RiskScore $riskScore
     * @param string $reason
     * @param int $statusCode
     * @param array $metadata
     */
    public function __construct(
        string $action,
        RiskScore $riskScore,
        string $reason = '',
        int $statusCode = 200,
        array $metadata = []
    ) {
        $this->action = $action;
        $this->riskScore = $riskScore;
        $this->reason = $reason;
        $this->statusCode = $statusCode;
        $this->metadata = $metadata;
    }

    public static function allow(RiskScore $riskScore = null, string $reason = 'Request passed all security checks.')
    {
        $score = $riskScore ? $riskScore : new RiskScore(0);
        return new static(self::ACTION_ALLOW, $score, $reason, 200);
    }

    public static function block(RiskScore $riskScore, string $reason = 'Request blocked by security policy.', int $statusCode = 403)
    {
        return new static(self::ACTION_BLOCK, $riskScore, $reason, $statusCode);
    }

    public static function throttle(RiskScore $riskScore, string $reason = 'Request rate limit exceeded.', int $statusCode = 429)
    {
        return new static(self::ACTION_THROTTLE, $riskScore, $reason, $statusCode);
    }

    public static function monitor(RiskScore $riskScore, string $reason = 'Potential threat detected (monitor mode).')
    {
        return new static(self::ACTION_MONITOR, $riskScore, $reason, 200);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getRiskScore(): RiskScore
    {
        return $this->riskScore;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isBlocked(): bool
    {
        return $this->action === self::ACTION_BLOCK;
    }

    public function isThrottled(): bool
    {
        return $this->action === self::ACTION_THROTTLE;
    }

    public function isAllowed(): bool
    {
        return $this->action === self::ACTION_ALLOW || $this->action === self::ACTION_MONITOR || $this->action === self::ACTION_LOG;
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'status_code' => $this->statusCode,
            'reason' => $this->reason,
            'risk_score' => $this->riskScore->toArray(),
            'metadata' => $this->metadata,
        ];
    }
}
