<?php

namespace Sagor\LaravelSecurity\Firewall;

use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Support\IpResolver;
use Sagor\LaravelSecurity\Support\PayloadNormalizer;

class SecurityContext
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string|null
     */
    protected $userId;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var array
     */
    protected $normalizedPayload;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * SecurityContext constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->ip = IpResolver::resolve($request);
        $this->userId = $this->resolveUserId($request);
        $this->route = $request->path();
        $this->method = strtoupper($request->method());
        $this->userAgent = (string) $request->header('User-Agent', '');
        $this->headers = $request->headers->all();
        $this->normalizedPayload = PayloadNormalizer::normalize($request);
        $this->timestamp = time();
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function resolveUserId(Request $request)
    {
        try {
            if ($request->user()) {
                return (string) $request->user()->getAuthIdentifier();
            }
        } catch (\Throwable $e) {
            // Ignore auth lookup errors
        }

        return null;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return string|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @return array
     */
    public function getNormalizedPayload(): array
    {
        return $this->normalizedPayload;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get attribute from context.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }

    /**
     * Set attribute in context.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
