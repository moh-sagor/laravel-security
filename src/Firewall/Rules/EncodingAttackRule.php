<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class EncodingAttackRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'encoding_attack.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects double-encoding, null-byte injection, and invalid character encoding attacks.';
    }

    /**
     * @param SecurityContext $context
     * @return SecurityRuleResult
     */
    public function check(SecurityContext $context): SecurityRuleResult
    {
        $payload = $context->getNormalizedPayload();

        foreach ($payload as $key => $value) {
            if (!is_string($value) || strlen($value) < 3) {
                continue;
            }

            // 1. Double URL encoding check (%25xx e.g. %2527 for ', %253c for <)
            if (preg_match('/%25[0-9a-f]{2}/i', $value)) {
                return SecurityRuleResult::threat(
                    $this->getId(),
                    'high',
                    0.92,
                    75,
                    'Double URL encoding attack detected in parameter: ' . $key,
                    ['param' => $key, 'pattern' => 'DOUBLE_ENCODING']
                );
            }

            // 2. Null byte attack (%00 or \0)
            if (strpos($value, "\0") !== false || strpos(strtolower($value), '%00') !== false) {
                return SecurityRuleResult::threat(
                    $this->getId(),
                    'critical',
                    0.95,
                    90,
                    'Null byte injection attack detected in parameter: ' . $key,
                    ['param' => $key, 'pattern' => 'NULL_BYTE']
                );
            }

            // 3. UTF-7 Encoding Attack (+ADw-script+AD4-)
            if (preg_match('/\+AD[w|4][a-z0-9\+\/]+-/i', $value)) {
                return SecurityRuleResult::threat(
                    $this->getId(),
                    'high',
                    0.95,
                    85,
                    'UTF-7 encoded XSS bypass vector detected in parameter: ' . $key,
                    ['param' => $key, 'pattern' => 'UTF7_ENCODING']
                );
            }
        }

        return SecurityRuleResult::clean($this->getId());
    }
}
