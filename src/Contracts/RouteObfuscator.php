<?php

namespace Sagor\LaravelSecurity\Contracts;

interface RouteObfuscator
{
    /**
     * Obfuscate a route name or URI into an alias.
     *
     * @param string $routeName
     * @return string
     */
    public function obfuscate(string $routeName): string;

    /**
     * Resolve an alias back to its original route or URI.
     *
     * @param string $alias
     * @return string|null
     */
    public function resolve(string $alias);
}
