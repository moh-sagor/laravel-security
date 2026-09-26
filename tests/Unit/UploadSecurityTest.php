<?php

namespace Sagor\LaravelSecurity\Tests\Unit;

use Sagor\LaravelSecurity\Tests\TestCase;
use Sagor\LaravelSecurity\Upload\FilenameValidator;
use Sagor\LaravelSecurity\Upload\FileSignatureValidator;

class UploadSecurityTest extends TestCase
{
    /** @test */
    public function it_rejects_dangerous_executable_extensions()
    {
        $this->assertFalse(FilenameValidator::isValid('shell.php'));
        $this->assertFalse(FilenameValidator::isValid('script.phar'));
        $this->assertFalse(FilenameValidator::isValid('payload.exe'));
    }

    /** @test */
    public function it_rejects_multi_extension_bypasses()
    {
        $this->assertFalse(FilenameValidator::isValid('avatar.php.jpg'));
        $this->assertFalse(FilenameValidator::isValid('image.phar.png'));
    }

    /** @test */
    public function it_validates_magic_byte_signatures()
    {
        $validJpgPath = __DIR__ . '/../Fixtures/uploads/test_jpg.jpg';
        $phpInJpgPath = __DIR__ . '/../Fixtures/uploads/test_php_in_jpg.jpg';

        $this->assertTrue(FileSignatureValidator::validate($validJpgPath, 'jpg'));
        $this->assertFalse(FileSignatureValidator::validate($phpInJpgPath, 'jpg'));
    }
}
