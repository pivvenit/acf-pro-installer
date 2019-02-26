<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test\Exceptions;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;

class MissingKeyExceptionTest extends TestCase
{
    public function testMessage()
    {
        $message = 'FIELD';
        $e = new MissingKeyException($message);
        $this->assertEquals(
            'Could not find a key for ACF PRO. ' .
            'Please make it available via the environment variable ' .
            $message,
            $e->getMessage()
        );
    }
}
