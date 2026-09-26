<?php

namespace Sagor\LaravelSecurity\Upload;

class ArchiveAnalyzer
{
    /**
     * Analyze archive file (ZIP) for decompression bombs, traversal, or script payload contents.
     *
     * @param string $filePath
     * @param array $config
     * @return array|null Null if safe, or threat detail array if malicious archive detected.
     */
    public static function analyze(string $filePath, array $config = [])
    {
        if (!class_exists('ZipArchive') || !file_exists($filePath)) {
            return null;
        }

        $maxFiles = isset($config['max_archive_files']) ? (int) $config['max_archive_files'] : 1000;
        $maxDecompressedSize = isset($config['max_archive_size']) ? (int) $config['max_archive_size'] : 104857600; // 100MB
        $maxRatio = isset($config['max_decompression_ratio']) ? (int) $config['max_decompression_ratio'] : 100;

        $compressedSize = filesize($filePath);
        if ($compressedSize <= 0) {
            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }

        $numFiles = $zip->numFiles;
        if ($numFiles > $maxFiles) {
            $zip->close();
            return [
                'threat' => 'Zip Bomb / Excessive File Count',
                'reason' => sprintf('Archive contains %d files (limit: %d).', $numFiles, $maxFiles),
            ];
        }

        $totalUncompressedSize = 0;
        $dangerousExts = ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'py', 'pl'];

        for ($i = 0; $i < $numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!$stat) {
                continue;
            }

            $entryName = $stat['name'];
            $entrySize = $stat['size'];
            $totalUncompressedSize += $entrySize;

            // 1. Path traversal inside archive entry name
            if (strpos($entryName, '../') !== false || strpos($entryName, '..\\') !== false) {
                $zip->close();
                return [
                    'threat' => 'Archive Path Traversal',
                    'reason' => 'Archive entry contains path traversal sequence: ' . $entryName,
                ];
            }

            // 2. Absolute path inside archive entry name
            if (strpos($entryName, '/') === 0 || preg_match('/^[a-z]:\\\\/i', $entryName)) {
                $zip->close();
                return [
                    'threat' => 'Archive Absolute Path Target',
                    'reason' => 'Archive entry contains absolute path: ' . $entryName,
                ];
            }

            // 3. Script / Executable file inside archive
            $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
            if (in_array($ext, $dangerousExts)) {
                $zip->close();
                return [
                    'threat' => 'Forbidden Script in Archive',
                    'reason' => 'Archive contains executable script file: ' . $entryName,
                ];
            }
        }

        $zip->close();

        // 4. Decompression bomb ratio check
        if ($totalUncompressedSize > $maxDecompressedSize) {
            return [
                'threat' => 'Decompression Bomb / Excessive Size',
                'reason' => sprintf('Decompressed archive size (%d bytes) exceeds maximum allowed (%d bytes).', $totalUncompressedSize, $maxDecompressedSize),
            ];
        }

        $ratio = $totalUncompressedSize / max(1, $compressedSize);
        if ($ratio > $maxRatio) {
            return [
                'threat' => 'Decompression Bomb / High Compression Ratio',
                'reason' => sprintf('Archive compression ratio (%.2fx) exceeds threshold (%dx).', $ratio, $maxRatio),
            ];
        }

        return null; // Safe archive
    }
}
