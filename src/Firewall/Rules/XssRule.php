<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class XssRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'xss.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects Cross-Site Scripting (XSS) payload patterns in HTTP requests.';
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
     * Inspect string value for XSS indicators.
     *
     * @param string $value
     * @param string $key
     * @return array|null
     */
    protected function inspectValue(string $value, string $key)
    {
        $decoded = rawurldecode(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $normalized = strtolower($decoded);

        // 1. Direct <script> tag detection
        if (preg_match('/<\s*script[^>]*>.*?/is', $normalized) || preg_match('/<\s*\/\s*script\s*>/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.99,
                'score' => 95,
                'message' => 'XSS (<script> tag) detected in parameter: ' . $key,
                'pattern' => 'SCRIPT_TAG',
            ];
        }

        // 2. Dangerous HTML event handlers (onload, onerror, onclick, etc.)
        if (preg_match('/<\s*[a-z0-9\-]+[^>]*?\b(on[a-z]{3,20})\s*=\s*[\'"]?.*?[^>]*>/i', $decoded)) {
            return [
                'severity' => 'high',
                'confidence' => 0.95,
                'score' => 90,
                'message' => 'XSS (Inline HTML Event Handler) detected in parameter: ' . $key,
                'pattern' => 'EVENT_HANDLER',
            ];
        }

        // 3. JavaScript / VBScript pseudo-protocols in src, href, action
        if (preg_match('/(javascript|vbscript|data):[^\s]*script/i', $normalized) ||
            preg_match('/(href|src)\s*=\s*[\'"]?\s*javascript:/i', $normalized)) {
            return [
                'severity' => 'high',
                'confidence' => 0.95,
                'score' => 85,
                'message' => 'XSS (javascript: URI scheme) detected in parameter: ' . $key,
                'pattern' => 'JAVASCRIPT_URI',
            ];
        }

        // 4. Dangerous tag vectors (iframe, object, embed, svg onload, body onload)
        if (preg_match('/<\s*(iframe|object|embed|applet|base|meta)\b[^>]*>/i', $normalized) ||
            preg_match('/<\s*svg[^>]*\bonload\s*=/i', $normalized)) {
            return [
                'severity' => 'high',
                'confidence' => 0.92,
                'score' => 80,
                'message' => 'XSS (Dangerous HTML Element Vector) detected in parameter: ' . $key,
                'pattern' => 'DANGEROUS_TAG',
            ];
        }

        // 5. Document object manipulation payloads (document.cookie, window.location, eval(), alert())
        if (preg_match('/(document\.cookie|document\.location|window\.location|eval\s*\(|alert\s*\()/i', $normalized) &&
            (strpos($normalized, '<') !== false || strpos($normalized, 'javascript:') !== false || strpos($normalized, 'onload') !== false)) {
            return [
                'severity' => 'high',
                'confidence' => 0.90,
                'score' => 85,
                'message' => 'XSS (DOM Manipulation Payload) detected in parameter: ' . $key,
                'pattern' => 'DOM_PAYLOAD',
            ];
        }

        return null;
    }
}
