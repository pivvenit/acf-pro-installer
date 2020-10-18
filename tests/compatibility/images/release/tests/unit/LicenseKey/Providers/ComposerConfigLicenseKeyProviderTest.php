<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\LicenseKey\Providers;

use Composer\Config;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\ComposerConfigLicenseKeyProvider;

class ComposerConfigLicenseKeyProviderTest extends TestCase
{
    public function testProvideWithLicenseKeyReturnsLicenseKey()
    {
        $key = '39b41b52-b404-4405-a9d2-904441363709'; // Don't bother, this key is not real
        $config = new Config();
        $config->merge(
            [
            'config' => [
                'acf-pro-key' => $key
            ]
            ]
        );
        $sut = new ComposerConfigLicenseKeyProvider($config);
        $this->assertEquals($key, $sut->provide());
    }

    public function testProvideWithoutLicenseKeyReturnsNull()
    {
        $config = new Config();
        $sut = new ComposerConfigLicenseKeyProvider($config);
        $this->assertNull($sut->provide());
    }

    public function testProvideWithObjectInKeyReturnsNull()
    {
        $config = new Config();
        $config->merge(
            [
            'config' => [
                'acf-pro-key' => (object)[
                    "invalid" => true,
                    "unexpected" => true
                ]
            ]
            ]
        );
        $sut = new ComposerConfigLicenseKeyProvider($config);
        $this->assertNull($sut->provide());
    }
}
