<?php

namespace Sagor\LaravelSecurity\Route;

class RouteResolver
{
    /**
     * Resolve alias path to original internal route.
     *
     * @param string $aliasPath
     * @return string|null
     */
    public function resolve(string $aliasPath)
    {
        return RouteMap::resolveOriginal($aliasPath);
    }
}
