<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class PathTraversalRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'path_traversal.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects path traversal and local file inclusion attempts in HTTP requests.';
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

            $threat = $this->inspectValue($value, $key);
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

        return SecurityRuleResult::clean($this->getId());
    }

    /**
     * Inspect string value for Path Traversal indicators.
     *
     * @param string $value
     * @param string $key
     * @return array|null
     */
    protected function inspectValue(string $value, string $key)
    {
        $decoded = rawurldecode(rawurldecode($value)); // Double decode check
        $normalized = str_replace('\\', '/', strtolower($decoded));

        // 1. Directory traversal sequences (../, ../../, ..%2f, etc.)
        if (preg_match('/(\.\.\/|\.\.\\\\|\%2e\%2e\%2f|\%2e\%2e\/|\%2e\%2e\\\\)/i', $value) ||
            strpos($normalized, '../..') !== false) {
            return [
                'severity' => 'critical',
                'confidence' => 0.98,
                'score' => 95,
                'message' => 'Path Traversal (../ directory sequence) detected in parameter: ' . $key,
                'pattern' => 'DIRECTORY_TRAVERSAL',
            ];
        }

        // 2. Sensitive system file targeting (/etc/passwd, /etc/shadow, win.ini, boot.ini, .env)
        if (preg_match('/(\/etc\/passwd|\/etc\/shadow|\/etc\/group|\/proc\/self\/environ|boot\.ini|win\.ini|\.env)/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.99,
                'score' => 98,
                'message' => 'Path Traversal / Local File Inclusion (System File Target) detected in parameter: ' . $key,
                'pattern' => 'SENSITIVE_FILE_TARGET',
            ];
        }

        // 3. Null-byte injection in path context
        if (strpos($value, "\0") !== false || strpos($value, '%00') !== false) {
            return [
                'severity' => 'critical',
                'confidence' => 0.95,
                'score' => 90,
                'message' => 'Null-byte injection in path context detected in parameter: ' . $key,
                'pattern' => 'NULL_BYTE_PATH',
            ];
        }

        // 4. PHP stream wrappers (php://filter, php://input, expect://, zip://, data://)
        if (preg_match('/(php|data|expect|zip|phar|glob):\/\//i', $normalized)) {
            return [
                'severity' => 'high',
                'confidence' => 0.95,
                'score' => 85,
                'message' => 'Remote/Local File Inclusion via PHP Stream Wrapper detected in parameter: ' . $key,
                'pattern' => 'PHP_WRAPPER',
            ];
        }

        return null;
    }
}
