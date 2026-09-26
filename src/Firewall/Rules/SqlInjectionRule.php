<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class SqlInjectionRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'sqli.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects SQL injection payload patterns in HTTP requests.';
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
     * Inspect string value for SQL injection indicators.
     *
     * @param string $value
     * @param string $key
     * @return array|null
     */
    protected function inspectValue(string $value, string $key)
    {
        // Normalize payload: decode URL encoding, lower case
        $decoded = rawurldecode($value);
        $normalized = strtolower(preg_replace('/\s+/', ' ', $decoded));

        // 1. UNION SELECT pattern
        if (preg_replace('/\/\*.*?\*\//', '', $normalized) && preg_match('/\bunion\b\s+(\bdistinct\b\s+|\ball\b\s+)?\bselect\b/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.99,
                'score' => 95,
                'message' => 'SQL Injection (UNION SELECT) detected in parameter: ' . $key,
                'pattern' => 'UNION_SELECT',
            ];
        }

        // 2. Tautology / Boolean-based blind patterns: OR '1'='1', OR 1=1, AND 1=1
        if (preg_match('/\b(or|and)\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i', $normalized) ||
            preg_match('/\b(or|and)\b\s+[\'"]?[a-z]+[\'"]?\s*=\s*[\'"]?[a-z]+[\'"]?/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.95,
                'score' => 90,
                'message' => 'SQL Injection (Tautology/Boolean) detected in parameter: ' . $key,
                'pattern' => 'TAUTOLOGY',
            ];
        }

        // 3. Time-based blind / Sleep / Benchmark / Heavy functions
        if (preg_match('/\b(sleep|benchmark|waitfor\s+delay|pg_sleep)\s*\(/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.98,
                'score' => 95,
                'message' => 'SQL Injection (Time-based Blind) detected in parameter: ' . $key,
                'pattern' => 'TIME_BASED',
            ];
        }

        // 4. Error-based functions (extractvalue, updatexml, load_file, @@version, user())
        if (preg_match('/\b(extractvalue|updatexml|load_file|into\s+outfile|into\s+dumpfile)\s*\(/i', $normalized) ||
            preg_match('/@@(version|hostname|datadir)/i', $normalized)) {
            return [
                'severity' => 'high',
                'confidence' => 0.95,
                'score' => 85,
                'message' => 'SQL Injection (Error-based/System Func) detected in parameter: ' . $key,
                'pattern' => 'ERROR_BASED',
            ];
        }

        // 5. Stacked query statements (e.g. '; drop table; --)
        if (preg_match('/;\s*(drop|alter|create|truncate|delete\s+from|insert\s+into)\s+/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.97,
                'score' => 95,
                'message' => 'SQL Injection (Stacked DDL/DML Command) detected in parameter: ' . $key,
                'pattern' => 'STACKED_QUERY',
            ];
        }

        // 6. SQL comment obfuscation with suspicious keywords
        if (preg_match('/\/\*!\d{5}.*?\*\//i', $decoded) ||
            preg_match('/\'\s*(--|#|\/\*)/i', $decoded)) {
            return [
                'severity' => 'high',
                'confidence' => 0.90,
                'score' => 80,
                'message' => 'SQL Injection (Comment Obfuscation) detected in parameter: ' . $key,
                'pattern' => 'COMMENT_OBFUSCATION',
            ];
        }

        return null;
    }
}
