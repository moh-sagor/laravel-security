<?php

namespace Sagor\LaravelSecurity\Upload;

class ScanResult
{
    const STATUS_CLEAN = 'clean';
    const STATUS_INFECTED = 'infected';
    const STATUS_UNAVAILABLE = 'scanner_unavailable';
    const STATUS_ERROR = 'error';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $threatName;

    /**
     * @var string
     */
    protected $scanner;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * ScanResult constructor.
     *
     * @param string $status
     * @param string|null $threatName
     * @param string $scanner
     * @param array $metadata
     */
    public function __construct(string $status, $threatName = null, string $scanner = 'clamav', array $metadata = [])
    {
        $this->status = $status;
        $this->threatName = $threatName;
        $this->scanner = $scanner;
        $this->metadata = $metadata;
    }

    public static function clean(string $scanner = 'clamav')
    {
        return new static(self::STATUS_CLEAN, null, $scanner);
    }

    public static function infected(string $threatName, string $scanner = 'clamav', array $metadata = [])
    {
        return new static(self::STATUS_INFECTED, $threatName, $scanner, $metadata);
    }

    public static function unavailable(string $scanner = 'clamav')
    {
        return new static(self::STATUS_UNAVAILABLE, null, $scanner);
    }

    public function isClean(): bool
    {
        return $this->status === self::STATUS_CLEAN;
    }

    public function isInfected(): bool
    {
        return $this->status === self::STATUS_INFECTED;
    }

    public function isAvailable(): bool
    {
        return $this->status !== self::STATUS_UNAVAILABLE;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getThreatName()
    {
        return $this->threatName;
    }

    public function getScanner(): string
    {
        return $this->scanner;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
