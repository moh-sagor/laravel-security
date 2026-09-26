<?php

namespace Sagor\LaravelSecurity\Firewall;

class SecurityRuleResult
{
    /**
     * @var bool
     */
    protected $detected;

    /**
     * @var string
     */
    protected $rule;

    /**
     * @var string
     */
    protected $severity;

    /**
     * @var float
     */
    protected $confidence;

    /**
     * @var int
     */
    protected $score;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * SecurityRuleResult constructor.
     *
     * @param bool $detected
     * @param string $rule
     * @param string $severity
     * @param float $confidence
     * @param int $score
     * @param string $message
     * @param array $metadata
     */
    public function __construct(
        bool $detected,
        string $rule,
        string $severity = 'medium',
        float $confidence = 1.0,
        int $score = 0,
        string $message = '',
        array $metadata = []
    ) {
        $this->detected = $detected;
        $this->rule = $rule;
        $this->severity = $severity;
        $this->confidence = $confidence;
        $this->score = $score;
        $this->message = $message;
        $this->metadata = $metadata;
    }

    /**
     * Helper to create a passing (clean) result.
     *
     * @param string $rule
     * @return static
     */
    public static function clean(string $rule)
    {
        return new static(false, $rule, 'low', 0.0, 0, 'Clean input.');
    }

    /**
     * Helper to create a threat detected result.
     *
     * @param string $rule
     * @param string $severity
     * @param float $confidence
     * @param int $score
     * @param string $message
     * @param array $metadata
     * @return static
     */
    public static function threat(
        string $rule,
        string $severity = 'high',
        float $confidence = 0.95,
        int $score = 50,
        string $message = 'Potential threat detected.',
        array $metadata = []
    ) {
        return new static(true, $rule, $severity, $confidence, $score, $message, $metadata);
    }

    public function isDetected(): bool
    {
        return $this->detected;
    }

    public function getRule(): string
    {
        return $this->rule;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getConfidence(): float
    {
        return $this->confidence;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'detected' => $this->detected,
            'rule' => $this->rule,
            'severity' => $this->severity,
            'confidence' => $this->confidence,
            'score' => $this->score,
            'message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
