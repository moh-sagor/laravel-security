<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:install {--force : Overwrite existing configuration files}';

    /**
     * @var string
     */
    protected $description = 'Install and initialize Laravel Security Firewall package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('====================================================');
        $this->info('      Laravel Security Firewall Installation        ');
        $this->info('====================================================');

        // 1. Publish Configuration
        $this->info('Publishing configuration files...');
        $params = ['--provider' => 'Sagor\LaravelSecurity\LaravelSecurityServiceProvider', '--tag' => 'config'];
        if ($this->option('force')) {
            $params['--force'] = true;
        }
        $this->call('vendor:publish', $params);
        $this->info('✓ Configuration published.');

        // 2. Publish Migrations
        $this->info('Publishing database migrations...');
        $this->call('vendor:publish', [
            '--provider' => 'Sagor\LaravelSecurity\LaravelSecurityServiceProvider',
            '--tag' => 'migrations',
        ]);
        $this->info('✓ Migrations published.');

        // 3. Create Security & Quarantine Directories
        $quarantineDir = storage_path('app/security/quarantine');
        if (!is_dir($quarantineDir)) {
            @mkdir($quarantineDir, 0750, true);
            @file_put_contents($quarantineDir . '/.htaccess', "Deny from all\n");
        }
        $this->info('✓ Security quarantine directories created.');

        // 4. Redis Detection
        try {
            if (class_exists('Illuminate\Support\Facades\Redis')) {
                \Illuminate\Support\Facades\Redis::ping();
                $this->info('✓ Redis cache system detected & available.');
            } else {
                $this->warn('! Redis driver not available. Using fallback cache driver.');
            }
        } catch (\Throwable $e) {
            $this->warn('! Redis not responding. Using fallback cache driver.');
        }

        $this->info('');
        $this->info('Shield & Security firewall installation completed successfully!');
        $this->info('Run "php artisan migrate" to create database security tables.');

        return 0;
    }
}
