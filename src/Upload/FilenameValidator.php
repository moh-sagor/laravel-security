<?php

namespace Sagor\LaravelSecurity\Upload;

class FilenameValidator
{
    /**
     * List of dangerous executable and script file extensions.
     *
     * @var array
     */
    protected static $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'cgi', 'pl', 'py', 'sh', 'bash', 'exe', 'dll', 'so', 'bin',
        'bat', 'cmd', 'vbs', 'asp', 'aspx', 'jsp', 'jspx', 'htaccess',
    ];

    /**
     * Validate filename for safety.
     *
     * @param string $filename
     * @return bool
     */
    public static function isValid(string $filename): bool
    {
        // 1. Check null bytes
        if (strpos($filename, "\0") !== false || strpos(strtolower($filename), '%00') !== false) {
            return false;
        }

        // 2. Check path traversal in filename
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return false;
        }

        // 3. Check double extension / multi-extension script injection (e.g. avatar.php.jpg)
        $parts = explode('.', strtolower($filename));
        if (count($parts) > 2) {
            foreach (array_slice($parts, 1, -1) as $middleExt) {
                if (in_array($middleExt, static::$dangerousExtensions)) {
                    return false; // Embedded script extension detected
                }
            }
        }

        // 4. Check final extension against dangerous script list
        $finalExt = end($parts);
        if (in_array($finalExt, static::$dangerousExtensions)) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize filename to alphanumeric safe format.
     *
     * @param string $filename
     * @return string
     */
    public static function sanitize(string $filename): string
    {
        $info = pathinfo($filename);
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', isset($info['filename']) ? $info['filename'] : 'file');
        $ext = strtolower(isset($info['extension']) ? $info['extension'] : 'bin');

        return $name . '.' . $ext;
    }
}
