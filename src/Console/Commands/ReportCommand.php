<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;
use Sagor\LaravelSecurity\Dashboard\DashboardManager;
use Sagor\LaravelSecurity\Support\CompatibilityHelper;

class ReportCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:report';

    /**
     * @var string
     */
    protected $description = 'Display security report summary for the application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $manager = new DashboardManager();
        $stats = $manager->getOverviewStats();
        $laravelVer = CompatibilityHelper::getLaravelVersion();
        $mode = config('security.mode', config('shield.mode', 'balanced'));

        $this->info('====================================================');
        $this->info('          Laravel Security Firewall Report          ');
        $this->info('====================================================');
        $this->line('Application:           Laravel ' . $laravelVer);
        $this->line('Package Version:       1.0.0 (sagor/laravel-security)');
        $this->line('Security Mode:         ' . $mode);
        $this->line('Threats Logged:        ' . number_format($stats['total_events']));
        $this->line('Blocked Requests:      ' . number_format($stats['total_blocked']));
        $this->line('Throttled Requests:    ' . number_format($stats['total_throttled']));
        $this->line('Malware Scans:         ' . number_format($stats['malware_scans_count']));

        return 0;
    }
}
