<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\IpManager;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class SsrfRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'ssrf.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects Server-Side Request Forgery (SSRF) payload vectors targeting internal networks or cloud metadata.';
    }

    /**
     * @param SecurityContext $context
     * @return SecurityRuleResult
     */
    public function check(SecurityContext $context): SecurityRuleResult
    {
        $payload = $context->getNormalizedPayload();
        $ssrfConfig = config('security.ssrf', config('shield.ssrf', []));

        $allowlist = isset($ssrfConfig['allowlist']) ? (array) $ssrfConfig['allowlist'] : [];

        foreach ($payload as $key => $value) {
            if (!is_string($value) || strlen($value) < 7) {
                continue;
            }

            // Inspect keys that look like URL fields or values containing http(s)://
            if (preg_match('/(url|uri|endpoint|link|host|target|site|fetch|redirect|webhook)/i', $key) ||
                preg_match('/^https?:\/\//i', $value)) {

                $threat = $this->inspectUrl($value, $key, $allowlist);
                if ($threat) {
                    return SecurityRuleResult::threat(
                        $this->getId(),
                        $threat['severity'],
                        $threat['confidence'],
                        $threat['score'],
                        $threat['message'],
                        ['param' => $key, 'pattern' => $threat['pattern']]
                    );
                }
            }
        }

        return SecurityRuleResult::clean($this->getId());
    }

    /**
     * Inspect URL string for SSRF targets.
     *
     * @param string $value
     * @param string $key
     * @param array $allowlist
     * @return array|null
     */
    protected function inspectUrl(string $value, string $key, array $allowlist)
    {
        $url = trim($value);
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return null;
        }

        $host = strtolower($host);

        // Check allowlist
        foreach ($allowlist as $allowedHost) {
            if ($host === strtolower($allowedHost)) {
                return null;
            }
        }

        // 1. Cloud Metadata Service Endpoint (169.254.169.254)
        if ($host === '169.254.169.254' || strpos($value, '169.254.169.254') !== false || $host === 'metadata.google.internal') {
            return [
                'severity' => 'critical',
                'confidence' => 0.99,
                'score' => 99,
                'message' => 'SSRF target targeting Cloud Instance Metadata Service (169.254.169.254) in parameter: ' . $key,
                'pattern' => 'CLOUD_METADATA',
            ];
        }

        // 2. Loopback / Localhost targets
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '0.0.0.0' || $host === '[::1]' || $host === '::1') {
            return [
                'severity' => 'critical',
                'confidence' => 0.98,
                'score' => 95,
                'message' => 'SSRF target targeting Localhost / Loopback interface in parameter: ' . $key,
                'pattern' => 'LOOPBACK_TARGET',
            ];
        }

        // 3. Private IP Subnet Ranges (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 127.0.0.0/8)
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.0/8',
            '169.254.0.0/16',
            'fc00::/7',
            'fe80::/10',
        ];

        foreach ($privateRanges as $range) {
            if (IpManager::matchIp($host, $range)) {
                return [
                    'severity' => 'critical',
                    'confidence' => 0.96,
                    'score' => 90,
                    'message' => 'SSRF target targeting Private IP Subnet (' . $range . ') in parameter: ' . $key,
                    'pattern' => 'PRIVATE_IP_TARGET',
                ];
            }
        }

        return null;
    }
}
