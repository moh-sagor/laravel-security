<?php

namespace Sagor\LaravelSecurity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\SecurityEngine;
use Sagor\LaravelSecurity\Support\CompatibilityHelper;

class SecurityMiddleware
{
    /**
     * @var SecurityEngine
     */
    protected $engine;

    /**
     * SecurityMiddleware constructor.
     *
     * @param SecurityEngine $engine
     */
    public function __construct(SecurityEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $decision = $this->engine->inspect($request);

        if ($decision->isBlocked()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return CompatibilityHelper::jsonResponse([
                    'message' => 'Request blocked by security policy.',
                    'code' => 'SECURITY_REQUEST_BLOCKED',
                ], $decision->getStatusCode());
            }

            if (view()->exists('laravel-security::blocked')) {
                return response(view('laravel-security::blocked', [
                    'reason' => $decision->getReason(),
                    'ip' => $request->ip(),
                ]), $decision->getStatusCode());
            }

            return response('Access Denied: Request blocked by security policy.', $decision->getStatusCode());
        }

        if ($decision->isThrottled()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return CompatibilityHelper::jsonResponse([
                    'message' => 'Too many requests. Please try again later.',
                    'code' => 'SECURITY_RATE_LIMITED',
                ], 429);
            }

            if (view()->exists('laravel-security::blocked')) {
                return response(view('laravel-security::blocked', [
                    'reason' => 'Too many requests. Rate limit threshold exceeded.',
                    'ip' => $request->ip(),
                ]), 429);
            }

            return response('Too Many Requests', 429);
        }

        $response = $next($request);

        // Attach HTTP Security Headers to outgoing response if enabled
        return $this->addSecurityHeaders($response);
    }

    /**
     * Add HTTP Security headers to response.
     *
     * @param mixed $response
     * @return mixed
     */
    protected function addSecurityHeaders($response)
    {
        $headersConfig = config('security.headers', config('shield.headers', []));

        if (empty($headersConfig) || (isset($headersConfig['enabled']) && !$headersConfig['enabled'])) {
            return $response;
        }

        if (!method_exists($response, 'header')) {
            return $response;
        }

        if (isset($headersConfig['hsts']) && $headersConfig['hsts']) {
            $maxAge = isset($headersConfig['hsts_max_age']) ? (int) $headersConfig['hsts_max_age'] : 31536000;
            $sub = isset($headersConfig['hsts_include_subdomains']) && $headersConfig['hsts_include_subdomains'] ? '; includeSubDomains' : '';
            $response->header('Strict-Transport-Security', 'max-age=' . $maxAge . $sub);
        }

        if (isset($headersConfig['content_type_options']) && $headersConfig['content_type_options']) {
            $response->header('X-Content-Type-Options', 'nosniff');
        }

        if (isset($headersConfig['frame_options']) && $headersConfig['frame_options']) {
            $response->header('X-Frame-Options', (string) $headersConfig['frame_options']);
        }

        if (isset($headersConfig['referrer_policy']) && $headersConfig['referrer_policy']) {
            $response->header('Referrer-Policy', (string) $headersConfig['referrer_policy']);
        }

        if (isset($headersConfig['permissions_policy']) && $headersConfig['permissions_policy']) {
            $response->header('Permissions-Policy', (string) $headersConfig['permissions_policy']);
        }

        return $response;
    }
}
