<?php

namespace Sagor\LaravelSecurity\Bot;

use Sagor\LaravelSecurity\Contracts\BotDetector as BotDetectorContract;
use Sagor\LaravelSecurity\Firewall\SecurityContext;

class BotDetector implements BotDetectorContract
{
    /**
     * @var array
     */
    protected $config;

    /**
     * BotDetector constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = !empty($config) ? $config : (array) config('security.bot', config('shield.bot', []));
    }

    /**
     * Analyze context for bot signatures and crawler patterns.
     *
     * @param SecurityContext $context
     * @return BotScore
     */
    public function analyze(SecurityContext $context): BotScore
    {
        $ua = strtolower($context->getUserAgent());
        $ip = $context->getIp();

        $trustedBots = isset($this->config['trusted_bots']) ? (array) $this->config['trusted_bots'] : [
            'googlebot', 'bingbot', 'duckduckbot', 'slurp', 'baiduspider', 'facebookexternalhit',
        ];

        $blockedBots = isset($this->config['blocked_bots']) ? (array) $this->config['blocked_bots'] : [
            'sqlmap', 'nikto', 'nmap', 'gobuster', 'dirbuster', 'acunetix', 'nessus',
        ];

        // 1. Check known malicious scanners
        foreach ($blockedBots as $blocked) {
            if (strpos($ua, strtolower($blocked)) !== false) {
                return new BotScore(1.0, true, false, $blocked);
            }
        }

        // 2. Check trusted search engine crawlers
        foreach ($trustedBots as $trusted) {
            if (strpos($ua, strtolower($trusted)) !== false) {
                return new BotScore(0.1, true, true, $trusted);
            }
        }

        // 3. Behavioral check for high frequency 404 enumeration
        $count404 = BehaviorAnalyzer::get404Count($ip);
        if ($count404 > 15) {
            return new BotScore(0.85, true, false, 'suspicious_scanner_behavior');
        }

        return new BotScore(0.0, false, false, null);
    }
}
