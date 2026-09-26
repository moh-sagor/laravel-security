<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;
use Sagor\LaravelSecurity\Models\SecurityEvent;

class CleanupCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:cleanup {--days= : Retention days override}';

    /**
     * @var string
     */
    protected $description = 'Clean up old security event records from the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days') ? (int) $this->option('days') : (int) config('security.logging.retention_days', 30);
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));

        $this->info(sprintf('Cleaning up security event records older than %d days (%s)...', $days, $cutoff));

        try {
            if (class_exists('Sagor\LaravelSecurity\Models\SecurityEvent')) {
                $deleted = SecurityEvent::where('created_at', '<', $cutoff)->delete();
                $this->info(sprintf('✓ Deleted %d old security event records.', $deleted));
            }
        } catch (\Throwable $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
        }

        return 0;
    }
}
