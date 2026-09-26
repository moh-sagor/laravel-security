<?php

namespace Sagor\LaravelSecurity\Facades;

use Illuminate\Support\Facades\Facade;
use Sagor\LaravelSecurity\Firewall\SecurityEngine;

/**
 * @method static \Sagor\LaravelSecurity\Firewall\SecurityDecision inspect(\Illuminate\Http\Request $request)
 * @method static \Sagor\LaravelSecurity\Firewall\RuleRegistry getRegistry()
 * @method static void addRule(\Sagor\LaravelSecurity\Contracts\SecurityRule $rule)
 */
class LaravelSecurity extends Facade
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
        /** @var SecurityEngine $engine */
        $engine = static::getFacadeRoot();
        if ($engine) {
            $engine->getRegistry()->addRule($rule);
        }
    }
}
