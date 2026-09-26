<?php

namespace Sagor\LaravelSecurity\Http\Controllers;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Models\SecurityEvent;
use Sagor\LaravelSecurity\Route\RouteEncryptor;
use Sagor\LaravelSecurity\Support\SecuritySanitizer;

class RouteEncryptionController
{
    /**
     * Handle incoming encrypted route requests.
     *
     * @param Request $request
     * @param string $token
     * @return mixed
     */
    public function handle(Request $request, string $token = '')
    {
        $encryptor = app(RouteEncryptor::class);
        $decrypted = $encryptor->decrypt($token);

        if (!$decrypted || empty($decrypted['path'])) {
            // Log security event for tampered or invalid encrypted route URL
            try {
                if (class_exists('Sagor\LaravelSecurity\Models\SecurityEvent')) {
                    SecurityEvent::create([
                        'event_id' => 'sec_' . uniqid() . '_' . bin2hex(random_bytes(4)),
                        'type' => 'route_encryption.invalid',
                        'severity' => 'high',
                        'confidence' => 1.0,
                        'risk_score' => 85,
                        'ip_hash' => class_exists('Sagor\LaravelSecurity\Support\SecuritySanitizer')
                            ? SecuritySanitizer::hashIp($request->ip())
                            : md5((string) $request->ip()),
                        'ip_address_encrypted' => function_exists('encrypt') ? encrypt($request->ip()) : $request->ip(),
                        'user_id' => method_exists($request, 'user') && $request->user() ? $request->user()->getKey() : null,
                        'route' => substr($request->path(), 0, 255),
                        'method' => $request->method(),
                        'user_agent_hash' => md5((string) $request->userAgent()),
                        'payload_hash' => md5($token),
                        'action' => 'block',
                        'metadata' => json_encode([
                            'reason' => 'Access Denied: Invalid, expired, or tampered encrypted route URL.',
                            'token' => substr($token, 0, 30) . '...',
                        ]),
                    ]);
                }
            } catch (\Throwable $e) {
                // Fail open
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Access Denied: Invalid or tampered encrypted route URL.',
                    'code' => 'INVALID_ENCRYPTED_ROUTE',
                ], 403);
            }

            if (view()->exists('laravel-security::blocked')) {
                return response(view('laravel-security::blocked', [
                    'reason' => 'SECURITY THREAT INTERCEPTED: Invalid, expired, or tampered encrypted route URL.',
                    'ip' => $request->ip(),
                ]), 403);
            }

            return response('Access Denied: Invalid or tampered encrypted route URL.', 403);
        }

        // Reconstruct internal path and merge decrypted query parameters
        $targetPath = $decrypted['path'];
        $mergedQuery = array_merge($request->query->all(), isset($decrypted['query']) ? $decrypted['query'] : []);

        if (!empty($mergedQuery)) {
            $targetPath .= '?' . http_build_query($mergedQuery);
        }

        // Duplicate request internally to target original URI
        $subRequest = Request::create(
            $targetPath,
            $request->getMethod(),
            $request->request->all(),
            $request->cookies->all(),
            $request->allFiles(),
            $request->server->all(),
            $request->getContent()
        );

        if ($request->hasSession()) {
            $subRequest->setLaravelSession($request->session());
        }

        $subRequest->setUserResolver(function () use ($request) {
            return $request->user();
        });

        // Set decrypted route attribute to prevent middleware re-encryption loop
        $subRequest->attributes->set('is_decrypted_route', true);

        // Dispatch sub-request to original target route controller
        return app('router')->dispatch($subRequest);
    }
}
