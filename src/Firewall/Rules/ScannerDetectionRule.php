<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class ScannerDetectionRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'scanner_detection.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects automated security scanners and enumeration tools based on request signatures.';
    }

    /**
     * @param SecurityContext $context
     * @return SecurityRuleResult
     */
    public function check(SecurityContext $context): SecurityRuleResult
    {
        $userAgent = strtolower($context->getUserAgent());
        $blockedBots = config('security.bot.blocked_bots', config('shield.bot.blocked_bots', [
            'sqlmap', 'nikto', 'nmap', 'gobuster', 'dirbuster',
            'acunetix', 'nessus', 'masscan', 'zgrab', 'w3af',
            'netsparker', 'openvas', 'havij',
        ]));

        foreach ($blockedBots as $scanner) {
            if (strpos($userAgent, strtolower($scanner)) !== false) {
                return SecurityRuleResult::threat(
                    $this->getId(),
                    'critical',
                    0.99,
                    95,
                    'Automated security scanner detected: ' . $scanner,
                    ['scanner' => $scanner, 'user_agent' => $context->getUserAgent()]
                );
            }
        }

        // Check suspicious scanner header anomalies (Acunetix-Aspect, X-Scanner, etc.)
        $headers = $context->getHeaders();
        foreach (['acunetix-aspect', 'x-scanner', 'x-originating-ip', 'x-w3af-opt'] as $scannerHeader) {
            if (isset($headers[$scannerHeader])) {
                return SecurityRuleResult::threat(
                    $this->getId(),
                    'critical',
                    0.99,
                    95,
                    'Security scanner header anomaly detected: ' . $scannerHeader,
                    ['header' => $scannerHeader]
                );
            }
        }

        return SecurityRuleResult::clean($this->getId());
    }
}
