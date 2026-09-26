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
use Sagor\LaravelSecurity\Http\Middleware\SecurityRouteEncryptionMiddleware;
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

        // 9. Register RouteEncryptor
        $this->app->singleton(\Sagor\LaravelSecurity\Route\RouteEncryptor::class, function ($app) {
            return new \Sagor\LaravelSecurity\Route\RouteEncryptor((array) config('security.route_encryption', []));
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

        // 2. Load Blade Views & Migrations
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-security');

        if (config('security.auto_load_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        // 3. Register Middleware aliases
        $router = $this->app['router'];
        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('security', SecurityMiddleware::class);
            $router->aliasMiddleware('shield', ShieldMiddleware::class);
            $router->aliasMiddleware('security.api', SecurityApiMiddleware::class);
            $router->aliasMiddleware('security.upload', SecurityUploadMiddleware::class);
            $router->aliasMiddleware('security.route_encryption', SecurityRouteEncryptionMiddleware::class);
        }

        // 4. Zero-Config Automatic Global & Group Middleware Injection
        $this->registerGlobalMiddleware();

        // 5. Register Dashboard & Cyber Desk routes
        $this->registerDashboardRoutes();

        // 6. Register Encrypted Route Interceptor Route
        $this->registerEncryptedRoutes();

        // 7. Register Blade Directives and URL Macros for Route Encryption
        $this->registerRouteEncryptionMacros();

        // 8. Extend URL Generator for automatic route() helper encryption
        if (config('security.route_encryption.enabled', true) && config('security.route_encryption.auto_encrypt_route_helper', true)) {
            $this->app->extend('url', function ($baseGenerator, $app) {
                if ($baseGenerator instanceof \Sagor\LaravelSecurity\Route\EncryptedUrlGenerator) {
                    return $baseGenerator;
                }
                return new \Sagor\LaravelSecurity\Route\EncryptedUrlGenerator(
                    $baseGenerator,
                    $app->make(\Sagor\LaravelSecurity\Route\RouteEncryptor::class)
                );
            });
        }
    }

    /**
     * Register Blade directives and URL macros for route encryption.
     *
     * @return void
     */
    protected function registerRouteEncryptionMacros()
    {
        if ($this->app->bound('blade.compiler')) {
            $blade = $this->app->make('blade.compiler');
            $blade->directive('encryptRoute', function ($expression) {
                return "<?php echo encrypt_route({$expression}); ?>";
            });
            $blade->directive('encryptUrl', function ($expression) {
                return "<?php echo encrypt_url({$expression}); ?>";
            });
        }

        if (class_exists(\Illuminate\Support\Facades\URL::class)) {
            \Illuminate\Support\Facades\URL::macro('encryptRoute', function ($name, $parameters = [], $absolute = true) {
                return encrypt_route($name, $parameters, $absolute);
            });
            \Illuminate\Support\Facades\URL::macro('encryptUrl', function ($path, $extra = [], $secure = null) {
                return encrypt_url($path, $extra, $secure);
            });
        }
    }

    /**
     * Automatically inject firewall & upload security middleware into HTTP kernel.
     *
     * @return void
     */
    protected function registerGlobalMiddleware()
    {
        if (!config('security.enabled', true)) {
            return;
        }

        if ($this->app->bound(\Illuminate\Contracts\Http\Kernel::class)) {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

            if (method_exists($kernel, 'prependMiddlewareToGroup')) {
                if (config('security.route_encryption.enabled', true)) {
                    $kernel->prependMiddlewareToGroup('web', SecurityRouteEncryptionMiddleware::class);
                }

                if (config('security.auto_apply_middleware', true)) {
                    $kernel->prependMiddlewareToGroup('api', SecurityApiMiddleware::class);
                    $kernel->prependMiddlewareToGroup('web', SecurityUploadMiddleware::class);
                    $kernel->prependMiddlewareToGroup('web', SecurityMiddleware::class);
                }
            } elseif (method_exists($kernel, 'pushMiddleware')) {
                if (config('security.auto_apply_middleware', true)) {
                    $kernel->pushMiddleware(SecurityMiddleware::class);
                }
            }
        }
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

        if (config('security.dashboard.require_auth', false) && !in_array('auth', $middleware)) {
            $middleware[] = 'auth';
        }

        $cleanPath = trim($path, '/');
        Route::middleware($middleware)->group(function () use ($cleanPath) {
            Route::get('/' . $cleanPath, [SecurityDashboardController::class, 'index'])->name('security.dashboard');
            Route::get('/' . $cleanPath . '/attempts', [SecurityDashboardController::class, 'attempts'])->name('security.attempts');
        });
    }

    /**
     * Register catch-all route for encrypted URLs (/e/{token}).
     *
     * @return void
     */
    protected function registerEncryptedRoutes()
    {
        if (!config('security.route_encryption.enabled', true)) {
            return;
        }

        $prefix = (string) config('security.route_encryption.prefix', 'e');
        $cleanPrefix = trim($prefix, '/');

        Route::middleware(['web'])->group(function () use ($cleanPrefix) {
            Route::any('/' . $cleanPrefix . '/{token}', [\Sagor\LaravelSecurity\Http\Controllers\RouteEncryptionController::class, 'handle'])
                ->where('token', '.*')
                ->name('security.encrypted_route');
        });
    }
}
