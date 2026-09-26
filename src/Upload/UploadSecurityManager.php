<?php

namespace Sagor\LaravelSecurity\Upload;

use Sagor\LaravelSecurity\Contracts\MalwareScanner;
use Sagor\LaravelSecurity\Events\MaliciousUploadDetected;
use Sagor\LaravelSecurity\Events\MalwareDetected;
use Sagor\LaravelSecurity\Support\SecurityLogger;
use Sagor\LaravelSecurity\Upload\Scanners\NullScanner;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadSecurityManager
{
    /**
     * @var MalwareScanner
     */
    protected $scanner;

    /**
     * @var array
     */
    protected $config;

    /**
     * UploadSecurityManager constructor.
     *
     * @param MalwareScanner|null $scanner
     * @param array $config
     */
    public function __construct(MalwareScanner $scanner = null, array $config = [])
    {
        $this->config = !empty($config) ? $config : (array) config('security.uploads', config('shield.uploads', []));
        $this->scanner = $scanner ? $scanner : new NullScanner();
    }

    /**
     * Process uploaded file through built-in magic-byte signature inspection & payload analyzer.
     *
     * @param UploadedFile $file
     * @return ScanResult
     */
    public function process(UploadedFile $file): ScanResult
    {
        $enabled = isset($this->config['enabled']) ? (bool) $this->config['enabled'] : true;
        if (!$enabled) {
            return ScanResult::clean('disabled');
        }

        $originalName = $file->getClientOriginalName();
        $realPath = $file->getRealPath();

        // Built-in Upload Inspection (Magic-byte signature, Filename, Size, Decompression bomb)
        $inspectionThreat = FileAnalyzer::inspect($file, $this->config);
        if ($inspectionThreat) {
            $reason = $inspectionThreat['threat'] . ': ' . $inspectionThreat['reason'];

            if (isset($this->config['quarantine']) && $this->config['quarantine'] && $realPath) {
                QuarantineManager::quarantine($realPath, $originalName, $reason, 'file_analyzer');
            }

            $this->dispatchEvents($file, $reason, 'file_analyzer');
            return ScanResult::infected($reason, 'file_analyzer');
        }

        return ScanResult::clean('file_analyzer');
    }

    /**
     * Dispatch upload security events.
     *
     * @param UploadedFile $file
     * @param string $threat
     * @param string $scanner
     * @return void
     */
    protected function dispatchEvents(UploadedFile $file, string $threat, string $scanner)
    {
        SecurityLogger::logError('[UPLOAD SECURITY THREAT] ' . $threat, [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'scanner' => $scanner,
        ]);

        try {
            if (function_exists('event')) {
                event(new MaliciousUploadDetected($file, $threat));
                event(new MalwareDetected($file, $threat, $scanner));
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}
