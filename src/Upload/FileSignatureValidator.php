<?php

namespace Sagor\LaravelSecurity\Upload;

class FileSignatureValidator
{
    /**
     * Map of file extension to magic byte signatures (hex patterns).
     *
     * @var array
     */
    protected static $signatures = [
        'jpg' => ['FFD8FF'],
        'jpeg' => ['FFD8FF'],
        'png' => ['89504E470D0A1A0A'],
        'gif' => ['474946383761', '474946383961'], // GIF87a, GIF89a
        'webp' => ['52494646'], // RIFF
        'pdf' => ['25504446'], // %PDF
        'zip' => ['504B0304', '504B0506', '504B0708'], // PK..
    ];

    /**
     * Validate file signature (magic bytes) against expected extension and inspect for web shell scripts inside images.
     *
     * @param string $filePath
     * @param string $extension
     * @return bool
     */
    public static function validate(string $filePath, string $extension): bool
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }

        $extension = strtolower(trim($extension));
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $bytes = fread($handle, 16);
        $contentSample = fread($handle, 4096);
        fclose($handle);

        $hex = strtoupper(bin2hex($bytes));

        // 1. Check if signature matches expected magic bytes if defined
        if (isset(static::$signatures[$extension])) {
            $matched = false;
            foreach (static::$signatures[$extension] as $sig) {
                if (strpos($hex, $sig) === 0) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return false;
            }
        }

        // 2. Reject image files containing embedded PHP script code (Web Shell in EXIF / JPG)
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $sample = strtolower($bytes . $contentSample);
            if (strpos($sample, '<?php') !== false || strpos($sample, '<script') !== false || strpos($sample, 'eval(') !== false) {
                return false;
            }
        }

        return true;
    }
}
