<?php

use Sagor\LaravelSecurity\Firewall\SecurityEngine;

if (!function_exists('security')) {
    /**
     * Get the Laravel Security Firewall engine instance or resolve security services.
     *
     * @return SecurityEngine|mixed
     */
    function security()
    {
        if (function_exists('app') && app()->bound(SecurityEngine::class)) {
            return app(SecurityEngine::class);
        }

        return null;
    }
}

if (!function_exists('shield')) {
    /**
     * Alias for security() helper.
     *
     * @return SecurityEngine|mixed
     */
    function shield()
    {
        return security();
    }
}
