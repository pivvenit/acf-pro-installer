<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\LicenseKey\Providers;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\ComposerConfigLicenseKeyProvider;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\CompositeLicenseKeyProvider;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DefaultLicenseKeyProviderFactory;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvLicenseKeyProvider;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\EnvironmentVariableLicenseKeyProvider;

class DefaultLicenseKeyProviderFactoryTest extends TestCase
{
    public function testBuildReturnsCompositeLicenseKeyProvider()
    {
        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())->method('getConfig')->willReturn(new Config());
        $io = $this->createMock(IOInterface::class);
        $sut = new DefaultLicenseKeyProviderFactory();
        /* @var CompositeLicenseKeyProvider $result */
        $result = $sut->build($composer, $io);
        $this->assertInstanceOf(CompositeLicenseKeyProvider::class, $result);
        $this->assertEquals(
            [
            DotEnvLicenseKeyProvider::class,
            EnvironmentVariableLicenseKeyProvider::class,
            ComposerConfigLicenseKeyProvider::class
            ],
            array_map(
                function ($provider) {
                    return get_class($provider);
                },
                $result->getProviders()
            )
        );
    }
}
