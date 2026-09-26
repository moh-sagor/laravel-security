<?php

namespace Sagor\LaravelSecurity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Route\RouteEncryptor;

class SecurityRouteEncryptionMiddleware
{
    /**
     * Handle incoming request and redirect unencrypted configured routes to their encrypted URL.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $enabled = config('security.route_encryption.enabled', true);
        if (!$enabled) {
            return $next($request);
        }

        // If request already came from internal decryption dispatch, allow through
        if ($request->attributes->get('is_decrypted_route', false)) {
            return $next($request);
        }

        $encryptor = app(RouteEncryptor::class);
        $path = '/' . ltrim($request->path(), '/');

        // Check if path is already encrypted (starts with prefix /e/)
        if ($encryptor->isAlreadyEncrypted($path)) {
            return $next($request);
        }

        $routeName = '';
        $route = $request->route();
        if ($route && method_exists($route, 'getName')) {
            $routeName = (string) $route->getName();
        }

        // Check if this route or path should be encrypted
        if ($encryptor->shouldEncryptRoute($routeName, $path)) {
            $query = $request->query->all();
            $encryptedPath = $encryptor->encrypt($path, $query);

            $scheme = $request->getScheme();
            $host = $request->getHttpHost();
            $encryptedUrl = $scheme . '://' . $host . $encryptedPath;

            return redirect()->to($encryptedUrl, 302);
        }

        return $next($request);
    }
}
