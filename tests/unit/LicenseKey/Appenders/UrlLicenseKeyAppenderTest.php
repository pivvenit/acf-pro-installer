<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\LicenseKey\Appenders;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Appenders\UrlLicenseKeyAppender;

class UrlLicenseKeyAppenderTest extends TestCase
{
    public function testAppend()
    {
        $sut = new UrlLicenseKeyAppender();
        $url = "https://example.com/download?version=1.0";
        $key = "5a66e8ff-8d6d-4d54-a15c-16746437562c";
        $this->assertEquals("{$url}&k={$key}", $sut->append($url, $key));
    }
}
