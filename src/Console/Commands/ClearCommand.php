<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:clear';

    /**
     * @var string
     */
    protected $description = 'Clear temporary security rate limiters and blocked IP cache counters';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Clearing temporary security firewall cache...');

        try {
            if (method_exists(Cache::getStore(), 'tags')) {
                try {
                    Cache::tags(['security', 'shield'])->flush();
                } catch (\Throwable $t) {
                    Cache::flush();
                }
            } else {
                Cache::flush();
            }
            $this->info('✓ Security firewall cache cleared successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to flush cache: ' . $e->getMessage());
        }

        return 0;
    }
}
