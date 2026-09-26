<?php

namespace Sagor\LaravelSecurity\Bot;

class BotScore
{
    /**
     * @var float
     */
    protected $score;

    /**
     * @var bool
     */
    protected $isBot;

    /**
     * @var bool
     */
    protected $isTrusted;

    /**
     * @var string|null
     */
    protected $botName;

    /**
     * BotScore constructor.
     *
     * @param float $score
     * @param bool $isBot
     * @param bool $isTrusted
     * @param string|null $botName
     */
    public function __construct(float $score = 0.0, bool $isBot = false, bool $isTrusted = false, $botName = null)
    {
        $this->score = min(1.0, max(0.0, $score));
        $this->isBot = $isBot;
        $this->isTrusted = $isTrusted;
        $this->botName = $botName;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function isBot(): bool
    {
        return $this->isBot;
    }

    public function isTrusted(): bool
    {
        return $this->isTrusted;
    }

    public function getBotName()
    {
        return $this->botName;
    }

    public function isMaliciousBot(): bool
    {
        return $this->isBot && !$this->isTrusted && $this->score >= 0.8;
    }
}
