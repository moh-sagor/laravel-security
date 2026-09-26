<?php

namespace Sagor\LaravelSecurity\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Sagor\LaravelSecurity\LaravelSecurityServiceProvider;
use Sagor\LaravelSecurity\ShieldServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Get package service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelSecurityServiceProvider::class,
            ShieldServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('security.enabled', true);
        $app['config']->set('security.mode', 'balanced');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
