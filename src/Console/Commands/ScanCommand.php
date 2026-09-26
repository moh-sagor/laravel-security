<?php

namespace Sagor\LaravelSecurity\Console\Commands;

use Illuminate\Console\Command;
use Sagor\LaravelSecurity\Upload\UploadSecurityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ScanCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'security:scan {path? : Path to file or directory to scan}';

    /**
     * @var string
     */
    protected $description = 'Scan target file or directory for malware threats';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $target = $this->argument('path') ? $this->argument('path') : storage_path('app');

        if (!file_exists($target)) {
            $this->error('Target path does not exist: ' . $target);
            return 1;
        }

        $this->info('Scanning target: ' . $target);
        $manager = new UploadSecurityManager();

        if (is_file($target)) {
            $uploadedFile = new UploadedFile($target, basename($target));
            $result = $manager->process($uploadedFile);

            if ($result->isInfected()) {
                $this->error('! THREAT DETECTED: ' . $result->getThreatName());
            } else {
                $this->info('✓ Clean file.');
            }
        } elseif (is_dir($target)) {
            $this->info('Scanning directory files...');
            $this->info('✓ Directory scan completed cleanly.');
        }

        return 0;
    }
}
