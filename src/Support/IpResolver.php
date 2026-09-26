<?php

namespace Sagor\LaravelSecurity\Support;

use Illuminate\Http\Request;

class IpResolver
{
    /**
     * Resolve the client IP address from HTTP request with proxy header validation.
     *
     * @param Request $request
     * @return string
     */
    public static function resolve(Request $request): string
    {
        $trustedConfig = config('security.trusted_proxies', config('shield.trusted_proxies', []));
        $headers = isset($trustedConfig['headers']) ? (array) $trustedConfig['headers'] : [
            'X-Forwarded-For',
            'CF-Connecting-IP',
            'X-Real-IP',
        ];

        foreach ($headers as $header) {
            $value = $request->header($header);
            if ($value) {
                // If multiple IPs present (e.g. "client, proxy1, proxy2"), extract first valid client IP
                $ips = explode(',', $value);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return (string) $request->ip();
    }
}
