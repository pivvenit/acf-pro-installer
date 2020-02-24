<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test\Exceptions;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;

class MissingKeyExceptionTest extends TestCase
{
    public function testMessage()
    {
        $message = 'testMessage';
        $e = new MissingKeyException($message);
        $this->assertEquals(
            "Could not find a license key for ACF PRO. {$message}",
            $e->getMessage()
        );
    }
}
