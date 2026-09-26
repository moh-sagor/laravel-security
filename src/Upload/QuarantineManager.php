<?php

namespace Sagor\LaravelSecurity\Upload;

use Illuminate\Support\Facades\Storage;
use Sagor\LaravelSecurity\Models\MalwareScan;

class QuarantineManager
{
    /**
     * Move a suspicious file into non-public quarantine storage directory.
     *
     * @param string $filePath
     * @param string $originalFilename
     * @param string $threatReason
     * @param string $scanner
     * @return string|null Path to quarantined file or null on failure
     */
    public static function quarantine(string $filePath, string $originalFilename, string $threatReason, string $scanner = 'clamav')
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $hash = sha1_file($filePath);
        $quarantineName = 'quarantine_' . time() . '_' . substr($hash, 0, 10) . '.bin';
        $quarantineDir = storage_path('app/security/quarantine');

        if (!is_dir($quarantineDir)) {
            @mkdir($quarantineDir, 0750, true);
            // Add .htaccess to block direct web server execution if inside storage
            @file_put_contents($quarantineDir . '/.htaccess', "Deny from all\n");
        }

        $targetPath = $quarantineDir . '/' . $quarantineName;
        $success = @copy($filePath, $targetPath);

        if (!$success) {
            return null;
        }

        // Store metadata JSON sidecar file
        $metadata = [
            'original_filename' => $originalFilename,
            'quarantine_filename' => $quarantineName,
            'hash' => $hash,
            'size' => filesize($filePath),
            'threat' => $threatReason,
            'scanner' => $scanner,
            'quarantined_at' => date('Y-m-d H:i:s'),
        ];
        @file_put_contents($targetPath . '.json', json_encode($metadata, JSON_PRETTY_PRINT));

        // Create database record if MalwareScan model exists
        try {
            if (class_exists('Sagor\LaravelSecurity\Models\MalwareScan')) {
                MalwareScan::create([
                    'file_name' => $originalFilename,
                    'file_hash' => $hash,
                    'file_size' => filesize($filePath),
                    'mime_type' => function_exists('mime_content_type') ? @mime_content_type($filePath) : 'unknown',
                    'status' => 'quarantined',
                    'scanner' => $scanner,
                    'threat_name' => $threatReason,
                    'quarantine_path' => $targetPath,
                ]);
            }
        } catch (\Throwable $e) {
            // Fail open on database error
        }

        return $targetPath;
    }
}
