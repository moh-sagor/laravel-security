<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;
use Sagor\LaravelSecurity\Route\RouteObfuscator;

class RoutesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:routes {--regenerate : Regenerate all obfuscated route mappings}';

    /**
     * @var string
     */
    protected $description = 'Generate and display obfuscated route mappings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $obfuscator = new RouteObfuscator();
        $configuredRoutes = config('security.route_obfuscation.routes', config('shield.route_obfuscation.routes', []));

        if (empty($configuredRoutes)) {
            $this->info('No routes configured for obfuscation in config/security.php.');
            return 0;
        }

        $this->info('Obfuscated Route Mappings:');
        $headers = ['Original Route', 'Obfuscated Alias'];
        $rows = [];

        foreach ($configuredRoutes as $route) {
            $alias = $obfuscator->obfuscate($route);
            $rows[] = [$route, '/' . $alias];
        }

        $this->table($headers, $rows);
        return 0;
    }
}
