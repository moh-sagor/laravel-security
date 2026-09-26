<?php

namespace Sagor\LaravelSecurity\Upload\Scanners;

use Sagor\LaravelSecurity\Contracts\MalwareScanner;
use Sagor\LaravelSecurity\Upload\ScanResult;

class NullScanner implements MalwareScanner
{
    /**
     * @param string $path
     * @return ScanResult
     */
    public function scan(string $path): ScanResult
    {
        return ScanResult::unavailable($this->getName());
    }

    public function isAvailable(): bool
    {
        return false;
    }

    public function getName(): string
    {
        return 'null';
    }
}
