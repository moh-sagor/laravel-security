<?php

namespace Sagor\LaravelSecurity\Events;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MaliciousUploadDetected
{
    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * @var string
     */
    public $threat;

    /**
     * MaliciousUploadDetected constructor.
     *
     * @param UploadedFile $file
     * @param string $threat
     */
    public function __construct(UploadedFile $file, string $threat)
    {
        $this->file = $file;
        $this->threat = $threat;
    }
}
