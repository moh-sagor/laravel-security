<?php

use Sagor\LaravelSecurity\Route\RouteEncryptor;

if (!function_exists('encrypt_route')) {
    /**
     * Generate an encrypted URL for a named route.
     *
     * @param string $name
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     */
    function encrypt_route(string $name, $parameters = [], bool $absolute = true): string
    {
        $url = route($name, $parameters, $absolute);
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = parse_url($url, PHP_URL_QUERY) ?: '';

        $queryParams = [];
        if (!empty($query)) {
            parse_str($query, $queryParams);
        }

        $encryptor = app(RouteEncryptor::class);
        $encryptedPath = $encryptor->encrypt($path, $queryParams);

        return url($encryptedPath, [], $absolute);
    }
}

if (!function_exists('encrypt_url')) {
    /**
     * Generate an encrypted URL for a given relative path.
     *
     * @param string $path
     * @param array $extra
     * @param bool|null $secure
     * @return string
     */
    function encrypt_url(string $path, array $extra = [], ?bool $secure = null): string
    {
        $url = url($path, $extra, $secure);
        $urlPath = parse_url($url, PHP_URL_PATH) ?: '/';
        $urlQuery = parse_url($url, PHP_URL_QUERY) ?: '';

        $queryParams = [];
        if (!empty($urlQuery)) {
            parse_str($urlQuery, $queryParams);
        }

        $encryptor = app(RouteEncryptor::class);
        $encryptedPath = $encryptor->encrypt($urlPath, $queryParams);

        return url($encryptedPath, [], $secure);
    }
}

if (!function_exists('decrypt_route_token')) {
    /**
     * Decrypt an encrypted route token.
     *
     * @param string $token
     * @return array|null
     */
    function decrypt_route_token(string $token): ?array
    {
        $encryptor = app(RouteEncryptor::class);
        return $encryptor->decrypt($token);
    }
}
