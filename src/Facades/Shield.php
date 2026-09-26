<?php

namespace Sagor\LaravelSecurity\Facades;

use Illuminate\Support\Facades\Facade;
use Sagor\LaravelSecurity\Firewall\SecurityEngine;

class Shield extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SecurityEngine::class;
    }

    /**
     * Helper to add custom rule to security firewall.
     *
     * @param \Sagor\LaravelSecurity\Contracts\SecurityRule $rule
     * @return void
     */
    public static function addRule($rule)
    {
        LaravelSecurity::addRule($rule);
    }
}
