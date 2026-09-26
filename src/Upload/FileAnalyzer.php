<?php

namespace Sagor\LaravelSecurity\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAnalyzer
{
    /**
     * Inspect uploaded file instance against security rules.
     *
     * @param UploadedFile $file
     * @param array $config
     * @return array|null Null if clean, or threat array details if malicious.
     */
    public static function inspect(UploadedFile $file, array $config = [])
    {
        $filename = $file->getClientOriginalName();
        $filePath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $maxKb = isset($config['max_size']) ? (int) $config['max_size'] : 20480; // 20MB
        $allowedExts = isset($config['allowed_extensions']) ? (array) $config['allowed_extensions'] : [];

        // 1. Filename & Extension Safety
        if (!FilenameValidator::isValid($filename)) {
            return [
                'threat' => 'Dangerous Filename / Script Extension',
                'reason' => 'Filename or extension contains script or multi-extension bypass: ' . $filename,
            ];
        }

        // 2. Allowed Extensions Whitelist Check if configured
        if (!empty($allowedExts) && !in_array($extension, $allowedExts)) {
            return [
                'threat' => 'Disallowed Extension',
                'reason' => sprintf('Extension .%s is not in the allowed file extensions list.', $extension),
            ];
        }

        // 3. File Size Validation
        $fileSizeKb = ceil($file->getSize() / 1024);
        if ($fileSizeKb > $maxKb) {
            return [
                'threat' => 'File Size Exceeded',
                'reason' => sprintf('Uploaded file size (%d KB) exceeds limit (%d KB).', $fileSizeKb, $maxKb),
            ];
        }

        // 4. File Magic Byte Signature Validation
        $validateSignature = isset($config['validate_signature']) ? (bool) $config['validate_signature'] : true;
        if ($validateSignature && $filePath) {
            if (!FileSignatureValidator::validate($filePath, $extension)) {
                return [
                    'threat' => 'File Signature Mismatch',
                    'reason' => sprintf('Magic byte signature does not match claimed .%s extension or contains embedded script.', $extension),
                ];
            }
        }

        // 5. Archive Analysis for ZIP files
        $archiveScan = isset($config['archive_scan']) ? (bool) $config['archive_scan'] : true;
        if ($archiveScan && $extension === 'zip' && $filePath) {
            $archiveThreat = ArchiveAnalyzer::analyze($filePath, $config);
            if ($archiveThreat) {
                return [
                    'threat' => $archiveThreat['threat'],
                    'reason' => $archiveThreat['reason'],
                ];
            }
        }

        return null; // Clean
    }
}
