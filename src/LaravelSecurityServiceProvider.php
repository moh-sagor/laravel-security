<?php

namespace Sagor\LaravelSecurity;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Sagor\LaravelSecurity\Console\Commands\CleanupCommand;
use Sagor\LaravelSecurity\Console\Commands\ClearCommand;
use Sagor\LaravelSecurity\Console\Commands\InstallCommand;
use Sagor\LaravelSecurity\Console\Commands\ReportCommand;
use Sagor\LaravelSecurity\Console\Commands\RoutesCommand;
use Sagor\LaravelSecurity\Console\Commands\ScanCommand;
use Sagor\LaravelSecurity\Console\Commands\StatusCommand;
use Sagor\LaravelSecurity\Console\Commands\TestCommand;
use Sagor\LaravelSecurity\Contracts\BotDetector as BotDetectorContract;
use Sagor\LaravelSecurity\Contracts\MalwareScanner;
use Sagor\LaravelSecurity\Contracts\RouteObfuscator as RouteObfuscatorContract;
use Sagor\LaravelSecurity\Firewall\RuleRegistry;
use Sagor\LaravelSecurity\Firewall\Rules\CommandInjectionRule;
use Sagor\LaravelSecurity\Firewall\Rules\EncodingAttackRule;
use Sagor\LaravelSecurity\Firewall\Rules\ParameterPollutionRule;
use Sagor\LaravelSecurity\Firewall\Rules\PathTraversalRule;
use Sagor\LaravelSecurity\Firewall\Rules\RequestSizeRule;
use Sagor\LaravelSecurity\Firewall\Rules\ScannerDetectionRule;
use Sagor\LaravelSecurity\Firewall\Rules\SqlInjectionRule;
use Sagor\LaravelSecurity\Firewall\Rules\SsrfRule;
use Sagor\LaravelSecurity\Firewall\Rules\SuspiciousUserAgentRule;
use Sagor\LaravelSecurity\Firewall\Rules\XssRule;
use Sagor\LaravelSecurity\Firewall\SecurityEngine;
use Sagor\LaravelSecurity\Firewall\SecurityPolicy;
use Sagor\LaravelSecurity\Http\Controllers\SecurityDashboardController;
use Sagor\LaravelSecurity\Http\Middleware\SecurityApiMiddleware;
use Sagor\LaravelSecurity\Http\Middleware\SecurityMiddleware;
use Sagor\LaravelSecurity\Http\Middleware\SecurityUploadMiddleware;
use Sagor\LaravelSecurity\Http\Middleware\ShieldMiddleware;
use Sagor\LaravelSecurity\RateLimit\RateLimiter;
use Sagor\LaravelSecurity\Route\RouteObfuscator;
use Sagor\LaravelSecurity\Upload\Scanners\NullScanner;
use Sagor\LaravelSecurity\Upload\UploadSecurityManager;

class LaravelSecurityServiceProvider extends ServiceProvider
{
    /**
     * Register package bindings and services.
     *
     * @return void
     */
    public function register()
    {
        // 1. Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/security.php', 'security');

        // 2. Register RuleRegistry and default security rules
        $this->app->singleton(RuleRegistry::class, function ($app) {
            $registry = new RuleRegistry();
            $registry->addRule(new SqlInjectionRule());
            $registry->addRule(new XssRule());
            $registry->addRule(new PathTraversalRule());
            $registry->addRule(new CommandInjectionRule());
            $registry->addRule(new SsrfRule());
            $registry->addRule(new ScannerDetectionRule());
            $registry->addRule(new SuspiciousUserAgentRule());
            $registry->addRule(new RequestSizeRule());
            $registry->addRule(new ParameterPollutionRule());
            $registry->addRule(new EncodingAttackRule());

            return $registry;
        });

        // 3. Register SecurityPolicy
        $this->app->singleton(SecurityPolicy::class, function ($app) {
            $mode = config('security.mode', 'balanced');
            $thresholds = (array) config('security.risk_thresholds', []);
            return new SecurityPolicy($mode, $thresholds);
        });

        // 4. Register SecurityEngine
        $this->app->singleton(SecurityEngine::class, function ($app) {
            return new SecurityEngine(
                $app->make(RuleRegistry::class),
                $app->make(SecurityPolicy::class),
                (array) config('security', [])
            );
        });

        // 5. Register RateLimiter
        $this->app->singleton(RateLimiter::class, function () {
            return new RateLimiter();
        });

        // 6. Register BotDetector
        $this->app->singleton(BotDetectorContract::class, function () {
            return new \Sagor\LaravelSecurity\Bot\BotDetector();
        });

        // 7. Register RouteObfuscator
        $this->app->singleton(RouteObfuscatorContract::class, function () {
            return new RouteObfuscator();
        });

        // 8. Register MalwareScanner & UploadSecurityManager
        $this->app->singleton(MalwareScanner::class, function () {
            return new NullScanner();
        });

        $this->app->singleton(UploadSecurityManager::class, function ($app) {
            return new UploadSecurityManager($app->make(MalwareScanner::class));
        });
    }

    /**
     * Bootstrap package publishing, views, commands, and routes.
     *
     * @return void
     */
    public function boot()
    {
        // 1. Publish assets and configs
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/security.php' => config_path('security.php'),
                __DIR__ . '/../config/shield.php' => config_path('shield.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-security'),
            ], 'views');

            // Register artisan commands
            $this->commands([
                InstallCommand::class,
                StatusCommand::class,
                ScanCommand::class,
                RoutesCommand::class,
                ClearCommand::class,
                ReportCommand::class,
                CleanupCommand::class,
                TestCommand::class,
            ]);
        }

        // 2. Load Blade Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-security');

        // 3. Register Middleware aliases
        $router = $this->app['router'];
        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('security', SecurityMiddleware::class);
            $router->aliasMiddleware('shield', ShieldMiddleware::class);
            $router->aliasMiddleware('security.api', SecurityApiMiddleware::class);
            $router->aliasMiddleware('security.upload', SecurityUploadMiddleware::class);
        }

        // 4. Register Dashboard routes
        $this->registerDashboardRoutes();
    }

    /**
     * Register security dashboard HTTP routes.
     *
     * @return void
     */
    protected function registerDashboardRoutes()
    {
        $path = (string) config('security.dashboard.path', 'security');
        $middleware = (array) config('security.dashboard.middleware', ['web']);

        $cleanPath = trim($path, '/');
        Route::middleware($middleware)->group(function () use ($cleanPath) {
            Route::get('/' . $cleanPath, [SecurityDashboardController::class, 'index'])->name('security.dashboard');
            Route::get('/' . $cleanPath . '/attempts', [SecurityDashboardController::class, 'attempts'])->name('security.attempts');
        });
    }
}
