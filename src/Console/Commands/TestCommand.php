<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Firewall\SecurityEngine;

class TestCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:test';

    /**
     * @var string
     */
    protected $description = 'Run interactive self-test on security firewall detectors';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Running Security Firewall Rule Engine Self-Tests...');

        $engine = app(SecurityEngine::class);

        $testPayloads = [
            'SQL Injection Test' => ['query' => "UNION SELECT 1, user(), version() --"],
            'XSS Attack Test' => ['body' => "<script>alert('xss')</script>"],
            'Path Traversal Test' => ['file' => "../../etc/passwd"],
            'SSRF Attack Test' => ['url' => "http://169.254.169.254/latest/meta-data/"],
        ];

        foreach ($testPayloads as $name => $input) {
            $request = Request::create('/test-endpoint', 'POST', $input);
            $decision = $engine->inspect($request);

            if ($decision->getRiskScore()->getScore() > 0) {
                $this->info('✓ ' . $name . ' -> DETECTED (Score: ' . $decision->getRiskScore()->getScore() . ')');
            } else {
                $this->warn('! ' . $name . ' -> NOT DETECTED');
            }
        }

        $this->info('Self-test complete.');
        return 0;
    }
}
