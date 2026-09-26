<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class CommandInjectionRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'command_injection.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects OS command injection payload patterns in HTTP requests.';
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
     * Inspect string value for command injection indicators.
     *
     * @param string $value
     * @param string $key
     * @return array|null
     */
    protected function inspectValue(string $value, string $key)
    {
        $decoded = rawurldecode($value);
        $normalized = strtolower($decoded);

        // 1. Shell chaining metacharacters combined with execution utilities (; cat, | bash, && curl, $(id), `whoami`)
        if (preg_match('/[;&|`]\s*(cat|ls|pwd|whoami|id|uname|curl|wget|nc|ncat|netcat|bash|sh|zsh|perl|python|ruby|powershell|cmd)\b/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.98,
                'score' => 95,
                'message' => 'Command Injection (Metacharacter + Command Execution) detected in parameter: ' . $key,
                'pattern' => 'COMMAND_CHAIN',
            ];
        }

        // 2. Subshell substitution: $(...) or `...`
        if (preg_match('/\$\([a-z0-9_\-\s\/]+\)/i', $decoded) || preg_match('/`[a-z0-9_\-\s\/]{3,}`/i', $decoded)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.95,
                'score' => 90,
                'message' => 'Command Injection (Subshell Command Substitution) detected in parameter: ' . $key,
                'pattern' => 'SUBSHELL',
            ];
        }

        // 3. Windows command execution vectors (cmd.exe /c, powershell -encodedcommand)
        if (preg_match('/(cmd\.exe|powershell\.exe|pwsh)\s+(\/c|-enc|-encodedcommand|-e)/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.99,
                'score' => 98,
                'message' => 'Command Injection (Windows CLI Execution) detected in parameter: ' . $key,
                'pattern' => 'WIN_CMD_EXEC',
            ];
        }

        // 4. Reverse shell payload signatures (/dev/tcp/, mkfifo, | sh)
        if (preg_match('/(\/dev\/tcp\/[0-9\.]+|mkfifo\s+[a-z0-9\/]+|nc\s+-e\s+\/bin\/sh)/i', $normalized)) {
            return [
                'severity' => 'critical',
                'confidence' => 0.99,
                'score' => 99,
                'message' => 'Command Injection (Reverse Shell Payload) detected in parameter: ' . $key,
                'pattern' => 'REVERSE_SHELL',
            ];
        }

        return null;
    }
}
