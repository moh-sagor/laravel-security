<?php

namespace Sagor\LaravelSecurity\Support;

class SecuritySanitizer
{
    /**
     * Redact sensitive fields from array or string context.
     *
     * @param array $data
     * @param array $sensitiveFields
     * @return array
     */
    public static function redactArray(array $data, array $sensitiveFields = []): array
    {
        if (empty($sensitiveFields)) {
            $sensitiveFields = (array) config('security.logging.redact_fields', [
                'password', 'password_confirmation', 'secret', 'api_key', 'token',
                'access_token', 'refresh_token', 'authorization', 'cookie',
                'credit_card', 'card_number', 'cvv', 'ssn',
            ]);
        }

        $redacted = [];

        foreach ($data as $key => $value) {
            $keyLower = strtolower((string) $key);
            $isSensitive = false;

            foreach ($sensitiveFields as $field) {
                if (strpos($keyLower, strtolower($field)) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $redacted[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $redacted[$key] = static::redactArray($value, $sensitiveFields);
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }

    /**
     * Anonymize or hash IP address for privacy compliance.
     *
     * @param string $ip
     * @return string
     */
    public static function hashIp(string $ip): string
    {
        $salt = config('app.key', 'laravel-security-salt');
        return hash_hmac('sha256', $ip, $salt);
    }
}
