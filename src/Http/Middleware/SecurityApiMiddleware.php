<?php

namespace Sagor\LaravelSecurity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\SecurityEngine;
use Sagor\LaravelSecurity\RateLimit\RateLimiter;
use Sagor\LaravelSecurity\Support\CompatibilityHelper;
use Sagor\LaravelSecurity\Support\IpResolver;

class SecurityApiMiddleware
{
    /**
     * @var SecurityEngine
     */
    protected $engine;

    /**
     * @var RateLimiter
     */
    protected $rateLimiter;

    /**
     * SecurityApiMiddleware constructor.
     *
     * @param SecurityEngine $engine
     * @param RateLimiter $rateLimiter
     */
    public function __construct(SecurityEngine $engine, RateLimiter $rateLimiter)
    {
        $this->engine = $engine;
        $this->rateLimiter = $rateLimiter;
    }

    /**
     * Handle an incoming API request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $apiConfig = config('security.api', config('shield.api', []));
        $enabled = isset($apiConfig['enabled']) ? $apiConfig['enabled'] : true;

        if ($enabled) {
            $ip = IpResolver::resolve($request);
            $rateLimit = isset($apiConfig['rate_limit']) ? (int) $apiConfig['rate_limit'] : 120;

            if (!$this->rateLimiter->attempt('api:' . $ip, $rateLimit, 60)) {
                return CompatibilityHelper::jsonResponse([
                    'message' => 'API rate limit exceeded. Too many requests.',
                    'code' => 'API_RATE_LIMIT_EXCEEDED',
                ], 429, [
                    'Retry-After' => 60,
                ]);
            }
        }

        $decision = $this->engine->inspect($request);
        if ($decision->isBlocked()) {
            return CompatibilityHelper::jsonResponse([
                'message' => 'Request blocked by security policy.',
                'code' => 'SECURITY_REQUEST_BLOCKED',
            ], $decision->getStatusCode());
        }

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $ip = IpResolver::resolve($request);
            $rateLimit = isset($apiConfig['rate_limit']) ? (int) $apiConfig['rate_limit'] : 120;
            $remaining = $this->rateLimiter->remaining('api:' . $ip, $rateLimit, 60);

            $response->header('X-RateLimit-Limit', $rateLimit);
            $response->header('X-RateLimit-Remaining', $remaining);
        }

        return $response;
    }
}
