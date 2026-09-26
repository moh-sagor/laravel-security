<?php

namespace Sagor\LaravelSecurity\Route;

use Illuminate\Support\Facades\Config;
use Sagor\LaravelSecurity\Support\SecuritySanitizer;

class RouteEncryptor
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $encryptionKey;

    /**
     * RouteEncryptor constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = !empty($config) ? $config : (array) config('security.route_encryption', []);
        
        $key = isset($this->config['key']) && !empty($this->config['key'])
            ? $this->config['key']
            : config('app.key', 'base64:SecurityDefaultFallbackKey32Chars!');

        if (strpos($key, 'base64:') === 0) {
            $key = base64_decode(substr($key, 7));
        }

        $this->encryptionKey = hash('sha256', $key, true);
    }

    /**
     * Encrypt a relative URL path or route parameters.
     *
     * @param string $path
     * @param array $query
     * @param int|null $ttlSeconds
     * @return string
     */
    public function encrypt(string $path, array $query = [], ?int $ttlSeconds = null): string
    {
        $prefix = isset($this->config['prefix']) ? trim($this->config['prefix'], '/') : 'e';
        
        $data = [
            'p' => $path,
            'q' => $query,
            't' => time(),
            'e' => $ttlSeconds ? (time() + $ttlSeconds) : null,
            'r' => bin2hex(random_bytes(4)),
        ];

        $json = json_encode($data);
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($json, 'AES-256-CBC', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
        $mac = hash_hmac('sha256', $iv . $ciphertext, $this->encryptionKey, true);

        $payload = $iv . substr($mac, 0, 16) . $ciphertext;
        $urlSafeToken = $this->base64UrlEncode($payload);

        return '/' . $prefix . '/' . $urlSafeToken;
    }

    /**
     * Decrypt an encrypted route token.
     *
     * @param string $token
     * @return array|null Returns ['path' => ..., 'query' => ...] or null if invalid
     */
    public function decrypt(string $token): ?array
    {
        $prefix = isset($this->config['prefix']) ? trim($this->config['prefix'], '/') : 'e';
        $token = preg_replace('/^' . preg_quote($prefix, '/') . '\//', '', ltrim($token, '/'));

        $raw = $this->base64UrlDecode($token);
        if (!$raw || strlen($raw) < 33) {
            return null;
        }

        $iv = substr($raw, 0, 16);
        $mac = substr($raw, 16, 16);
        $ciphertext = substr($raw, 32);

        $calculatedMac = substr(hash_hmac('sha256', $iv . $ciphertext, $this->encryptionKey, true), 0, 16);
        if (!hash_equals($mac, $calculatedMac)) {
            return null; // Tampered token
        }

        $json = openssl_decrypt($ciphertext, 'AES-256-CBC', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['p'])) {
            return null;
        }

        // Expiration check
        if (!empty($data['e']) && time() > $data['e']) {
            return null; // Expired link
        }

        return [
            'path' => $data['p'],
            'query' => isset($data['q']) && is_array($data['q']) ? $data['q'] : [],
            'timestamp' => isset($data['t']) ? $data['t'] : null,
        ];
    }

    /**
     * Check if a path is already encrypted (starts with prefix).
     *
     * @param string $path
     * @return bool
     */
    public function isAlreadyEncrypted(string $path): bool
    {
        $prefix = isset($this->config['prefix']) ? trim($this->config['prefix'], '/') : 'e';
        $cleanPath = ltrim($path, '/');
        return strpos($cleanPath, $prefix . '/') === 0;
    }

    /**
     * Determine if a route name or URI should be encrypted based on configuration.
     *
     * @param string $routeName
     * @param string $path
     * @return bool
     */
    public function shouldEncryptRoute(string $routeName, string $path = ''): bool
    {
        if (empty($this->config['enabled'])) {
            return false;
        }

        if ($this->isAlreadyEncrypted($path)) {
            return false;
        }

        // Check exclusions first
        $exclude = isset($this->config['exclude']) ? (array) $this->config['exclude'] : [];
        foreach ($exclude as $pattern) {
            if ($this->matchPattern($pattern, $routeName) || $this->matchPattern($pattern, $path)) {
                return false;
            }
        }

        // Check target encryption routes
        $targetRoutes = isset($this->config['routes']) ? (array) $this->config['routes'] : [];
        foreach ($targetRoutes as $pattern) {
            if ($this->matchPattern($pattern, $routeName) || $this->matchPattern($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match pattern against subject using wildcards.
     *
     * @param string $pattern
     * @param string $subject
     * @return bool
     */
    protected function matchPattern(string $pattern, string $subject): bool
    {
        if (empty($subject) || empty($pattern)) {
            return false;
        }

        if ($pattern === $subject) {
            return true;
        }

        if (strpos($pattern, '*') !== false) {
            $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/i';
            return (bool) preg_match($regex, $subject);
        }

        return false;
    }

    /**
     * URL-safe base64 encode.
     *
     * @param string $data
     * @return string
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * URL-safe base64 decode.
     *
     * @param string $data
     * @return string|false
     */
    protected function base64UrlDecode(string $data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
