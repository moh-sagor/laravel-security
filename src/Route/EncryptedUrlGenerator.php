<?php

namespace Sagor\LaravelSecurity\Route;

use Illuminate\Routing\UrlGenerator;

class EncryptedUrlGenerator extends UrlGenerator
{
    /**
     * @var UrlGenerator
     */
    protected $baseGenerator;

    /**
     * @var RouteEncryptor
     */
    protected $encryptor;

    /**
     * Recursion prevention flag.
     *
     * @var bool
     */
    protected static $isProcessing = false;

    /**
     * EncryptedUrlGenerator constructor.
     *
     * @param UrlGenerator $baseGenerator
     * @param RouteEncryptor $encryptor
     */
    public function __construct(UrlGenerator $baseGenerator, RouteEncryptor $encryptor)
    {
        if (method_exists($baseGenerator, 'getRoutes') && method_exists($baseGenerator, 'getRequest')) {
            parent::__construct(
                $baseGenerator->getRoutes(),
                $baseGenerator->getRequest(),
                method_exists($baseGenerator, 'getAssetOrigin') ? $baseGenerator->getAssetOrigin() : null
            );
        }
        $this->baseGenerator = $baseGenerator;
        $this->encryptor = $encryptor;
    }

    /**
     * Generate a URL for a named route, automatically encrypting if matching target rules.
     *
     * @param string $name
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        if (static::$isProcessing) {
            return $this->baseGenerator->route($name, $parameters, $absolute);
        }

        static::$isProcessing = true;

        try {
            $url = $this->baseGenerator->route($name, $parameters, $absolute);

            $parsedPath = parse_url($url, PHP_URL_PATH) ?: '/';
            $parsedQuery = parse_url($url, PHP_URL_QUERY) ?: '';

            if ($this->encryptor->shouldEncryptRoute($name, $parsedPath)) {
                $queryParams = [];
                if (!empty($parsedQuery)) {
                    parse_str($parsedQuery, $queryParams);
                }

                $encryptedPath = $this->encryptor->encrypt($parsedPath, $queryParams);

                if ($absolute) {
                    $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'http';
                    $host = parse_url($url, PHP_URL_HOST) ?: 'localhost';
                    $port = parse_url($url, PHP_URL_PORT);
                    $portStr = $port ? ':' . $port : '';
                    return $scheme . '://' . $host . $portStr . $encryptedPath;
                }

                return $encryptedPath;
            }

            return $url;
        } finally {
            static::$isProcessing = false;
        }
    }

    /**
     * Generate a URL to a given path, encrypting if matching target rules.
     *
     * @param string $path
     * @param mixed $extra
     * @param bool|null $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        if (static::$isProcessing) {
            return $this->baseGenerator->to($path, $extra, $secure);
        }

        static::$isProcessing = true;

        try {
            $url = $this->baseGenerator->to($path, $extra, $secure);

            $parsedPath = parse_url($url, PHP_URL_PATH) ?: '/';
            $parsedQuery = parse_url($url, PHP_URL_QUERY) ?: '';

            if ($this->encryptor->shouldEncryptRoute('', $parsedPath)) {
                $queryParams = [];
                if (!empty($parsedQuery)) {
                    parse_str($parsedQuery, $queryParams);
                }

                $encryptedPath = $this->encryptor->encrypt($parsedPath, $queryParams);

                if ($secure === true || (is_null($secure) && strpos($url, 'https://') === 0)) {
                    $scheme = 'https';
                } else {
                    $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'http';
                }
                $host = parse_url($url, PHP_URL_HOST) ?: 'localhost';
                $port = parse_url($url, PHP_URL_PORT);
                $portStr = $port ? ':' . $port : '';

                return $scheme . '://' . $host . $portStr . $encryptedPath;
            }

            return $url;
        } finally {
            static::$isProcessing = false;
        }
    }

    /**
     * Forward all other UrlGenerator calls to base generator.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->baseGenerator, $method], $parameters);
    }
}
