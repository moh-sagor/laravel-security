<?php

namespace Sagor\LaravelSecurity\Firewall;

class RiskScore
{
    /**
     * @var int
     */
    protected $score;

    /**
     * @var string
     */
    protected $level;

    /**
     * @var SecurityRuleResult[]
     */
    protected $triggeredResults = [];

    /**
     * RiskScore constructor.
     *
     * @param int $score
     * @param array $triggeredResults
     */
    public function __construct(int $score, array $triggeredResults = [])
    {
        $this->score = min(100, max(0, $score));
        $this->triggeredResults = $triggeredResults;
        $this->level = $this->calculateLevel($this->score);
    }

    /**
     * @param int $score
     * @return string
     */
    protected function calculateLevel(int $score): string
    {
        if ($score >= 80) {
            return 'CRITICAL';
        }
        if ($score >= 60) {
            return 'HIGH';
        }
        if ($score >= 30) {
            return 'MEDIUM';
        }
        return 'LOW';
    }

    /**
     * Factory to build RiskScore from array of SecurityRuleResult.
     *
     * @param SecurityRuleResult[] $results
     * @return static
     */
    public static function fromRuleResults(array $results)
    {
        $totalScore = 0;
        $triggered = [];

        foreach ($results as $result) {
            if ($result instanceof SecurityRuleResult && $result->isDetected()) {
                $triggered[] = $result;
                $effectiveScore = (int) round($result->getScore() * $result->getConfidence());
                $totalScore += $effectiveScore;
            }
        }

        return new static($totalScore, $triggered);
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @return SecurityRuleResult[]
     */
    public function getTriggeredResults(): array
    {
        return $this->triggeredResults;
    }

    public function isHighRisk(): bool
    {
        return $this->score >= 60;
    }

    public function isCritical(): bool
    {
        return $this->score >= 80;
    }

    public function toArray(): array
    {
        $rules = [];
        foreach ($this->triggeredResults as $res) {
            $rules[] = $res->toArray();
        }

        return [
            'score' => $this->score,
            'level' => $this->level,
            'triggered_count' => count($this->triggeredResults),
            'triggered_rules' => $rules,
        ];
    }
}
