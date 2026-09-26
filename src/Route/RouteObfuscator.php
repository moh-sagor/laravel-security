<?php

namespace Sagor\LaravelSecurity\Route;

use Sagor\LaravelSecurity\Contracts\RouteObfuscator as RouteObfuscatorContract;

class RouteObfuscator implements RouteObfuscatorContract
{
    /**
     * @var array
     */
    protected $config;

    /**
     * RouteObfuscator constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = !empty($config) ? $config : (array) config('security.route_obfuscation', config('shield.route_obfuscation', []));
    }

    /**
     * Obfuscate a route name or URI.
     *
     * @param string $routeName
     * @return string
     */
    public function obfuscate(string $routeName): string
    {
        if ($this->isExcluded($routeName)) {
            return $routeName;
        }

        $existing = RouteMap::getAlias($routeName);
        if ($existing) {
            return $existing;
        }

        $prefix = isset($this->config['prefix']) ? $this->config['prefix'] : 'r';
        $random = bin2hex(random_bytes(6));
        $alias = $prefix . '/' . $random;

        RouteMap::setMapping($routeName, $alias);

        return $alias;
    }

    /**
     * Resolve alias path back to original route.
     *
     * @param string $alias
     * @return string|null
     */
    public function resolve(string $alias)
    {
        return RouteMap::resolveOriginal($alias);
    }

    /**
     * Check if route name or path is excluded from obfuscation.
     *
     * @param string $routeName
     * @return bool
     */
    public function isExcluded(string $routeName): bool
    {
        $excluded = isset($this->config['exclude']) ? (array) $this->config['exclude'] : [
            'login', 'logout', 'password.*', 'api.*', 'webhooks.*',
        ];

        foreach ($excluded as $pattern) {
            if ($routeName === $pattern) {
                return true;
            }
            if (strpos($pattern, '*') !== false) {
                $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/i';
                if (preg_match($regex, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }
}
