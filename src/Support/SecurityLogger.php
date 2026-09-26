<?php

namespace Sagor\LaravelSecurity\Support;

use Illuminate\Support\Facades\Log;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityDecision;
use Sagor\LaravelSecurity\Models\SecurityEvent;

class SecurityLogger
{
    /**
     * Log a security threat event to configured log channel and database.
     *
     * @param SecurityContext $context
     * @param SecurityDecision $decision
     * @return void
     */
    public static function logThreat(SecurityContext $context, SecurityDecision $decision)
    {
        $logConfig = config('security.logging', config('shield.logging', []));
        $enabled = isset($logConfig['enabled']) ? $logConfig['enabled'] : true;

        if (!$enabled) {
            return;
        }

        $riskScore = $decision->getRiskScore();
        $triggeredRules = $riskScore->getTriggeredResults();
        $primaryRule = !empty($triggeredRules) ? $triggeredRules[0]->getRule() : 'security.unknown';

        $logData = [
            'event_id' => 'sec_' . uniqid() . '_' . bin2hex(random_bytes(4)),
            'type' => $primaryRule,
            'severity' => strtolower($riskScore->getLevel()),
            'confidence' => !empty($triggeredRules) ? $triggeredRules[0]->getConfidence() : 1.0,
            'risk_score' => $riskScore->getScore(),
            'ip' => $context->getIp(),
            'user_id' => $context->getUserId(),
            'route' => $context->getRoute(),
            'method' => $context->getMethod(),
            'action' => $decision->getAction(),
            'reason' => $decision->getReason(),
            'user_agent' => $context->getUserAgent(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // 1. Structured log file output
        try {
            $channel = isset($logConfig['channel']) ? $logConfig['channel'] : 'daily';
            Log::channel($channel)->warning('[SECURITY FIREWALL THREAT]', SecuritySanitizer::redactArray($logData));
        } catch (\Throwable $e) {
            // Fallback to default logger
            try {
                Log::warning('[SECURITY FIREWALL THREAT]', SecuritySanitizer::redactArray($logData));
            } catch (\Throwable $ex) {
                // Ignore log write failure
            }
        }

        // 2. Database record creation if database logging is enabled
        $dbLogging = isset($logConfig['database']) ? $logConfig['database'] : true;
        if ($dbLogging) {
            try {
                if (class_exists('Sagor\LaravelSecurity\Models\SecurityEvent')) {
                    SecurityEvent::create([
                        'event_id' => $logData['event_id'],
                        'type' => $logData['type'],
                        'severity' => $logData['severity'],
                        'confidence' => $logData['confidence'],
                        'risk_score' => $logData['risk_score'],
                        'ip_hash' => SecuritySanitizer::hashIp($context->getIp()),
                        'ip_address_encrypted' => function_exists('encrypt') ? encrypt($context->getIp()) : $context->getIp(),
                        'user_id' => $context->getUserId(),
                        'route' => substr($context->getRoute(), 0, 255),
                        'method' => $context->getMethod(),
                        'user_agent_hash' => md5($context->getUserAgent()),
                        'payload_hash' => md5(json_encode($context->getNormalizedPayload())),
                        'action' => $decision->getAction(),
                        'metadata' => json_encode(SecuritySanitizer::redactArray($decision->toArray())),
                    ]);
                }
            } catch (\Throwable $e) {
                // Fail open on database error
            }
        }
    }

    /**
     * Log system-level security engine exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function logError(string $message, array $context = [])
    {
        try {
            Log::error('[SECURITY FIREWALL ERROR] ' . $message, $context);
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}
