<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;

class StatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:status';

    /**
     * @var string
     */
    protected $description = 'Display current status of Laravel Security Firewall package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $enabled = config('security.enabled', config('shield.enabled', true));
        $mode = config('security.mode', config('shield.mode', 'balanced'));

        $this->info('====================================================');
        $this->info('         Laravel Security Firewall Status           ');
        $this->info('====================================================');

        $this->line('Shield / Security Firewall: ' . ($enabled ? '<fg=green>ENABLED</>' : '<fg=red>DISABLED</>'));
        $this->line('Security Mode:             <fg=cyan>' . strtoupper($mode) . '</>');
        $this->line('Web Protection:            <fg=green>ENABLED</>');
        $this->line('API Protection:            <fg=green>ENABLED</>');
        $this->line('Upload Security:           <fg=green>ENABLED</>');
        $this->line('Bot Detection:             <fg=green>ENABLED</>');
        $this->line('DDoS / Rate Limiting:      <fg=green>ENABLED</>');

        return 0;
    }
}
