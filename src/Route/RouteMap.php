<?php

namespace Sagor\LaravelSecurity\Route;

use Illuminate\Support\Facades\Cache;

class RouteMap
{
    /**
     * Get alias for route name or path.
     *
     * @param string $original
     * @return string|null
     */
    public static function getAlias(string $original)
    {
        $cacheKey = 'security:routemap:orig:' . md5($original);
        if (Cache::has($cacheKey)) {
            return (string) Cache::get($cacheKey);
        }

        try {
            if (class_exists('Sagor\LaravelSecurity\Models\SecurityRouteMap')) {
                $record = \Sagor\LaravelSecurity\Models\SecurityRouteMap::where('original_route', $original)->first();
                if ($record) {
                    Cache::put($cacheKey, $record->alias_path, 86400);
                    return $record->alias_path;
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return null;
    }

    /**
     * Resolve original route from alias path.
     *
     * @param string $alias
     * @return string|null
     */
    public static function resolveOriginal(string $alias)
    {
        $cacheKey = 'security:routemap:alias:' . md5($alias);
        if (Cache::has($cacheKey)) {
            return (string) Cache::get($cacheKey);
        }

        try {
            if (class_exists('Sagor\LaravelSecurity\Models\SecurityRouteMap')) {
                $record = \Sagor\LaravelSecurity\Models\SecurityRouteMap::where('alias_path', $alias)->first();
                if ($record) {
                    Cache::put($cacheKey, $record->original_route, 86400);
                    return $record->original_route;
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return null;
    }

    /**
     * Store mapping between original route and obfuscated alias.
     *
     * @param string $original
     * @param string $alias
     * @return void
     */
    public static function setMapping(string $original, string $alias)
    {
        Cache::put('security:routemap:orig:' . md5($original), $alias, 86400);
        Cache::put('security:routemap:alias:' . md5($alias), $original, 86400);

        try {
            if (class_exists('Sagor\LaravelSecurity\Models\SecurityRouteMap')) {
                \Sagor\LaravelSecurity\Models\SecurityRouteMap::updateOrCreate(
                    ['original_route' => $original],
                    ['alias_path' => $alias, 'hash' => md5($original)]
                );
            }
        } catch (\Throwable $e) {
            // Ignore DB error
        }
    }
}
