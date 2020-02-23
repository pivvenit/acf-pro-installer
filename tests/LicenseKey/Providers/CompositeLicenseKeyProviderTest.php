<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\LicenseKey\Providers;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\CompositeLicenseKeyProvider;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\LicenseKeyProviderInterface;

class CompositeLicenseKeyProviderTest extends TestCase
{
    public function testProvideWithoutProvidersReturnsNull()
    {
        $sut = new CompositeLicenseKeyProvider();
        $this->assertNull($sut->provide());
    }

    public function testProvideCallsProviders()
    {
        $key = '35660a21-b857-4d9c-aed9-9ad5974bda96';
        $provider1 = $this->createMock(LicenseKeyProviderInterface::class);
        $provider1->expects($this->once())->method('provide')->willReturn($key);

        $sut = new CompositeLicenseKeyProvider($provider1);
        $this->assertEquals($key, $sut->provide());
    }

    public function testProvideDoesNotCallProvidersAfterProviderWithResult()
    {
        $key = '35660a21-b857-4d9c-aed9-9ad5974bda96';
        $provider1 = $this->createMock(LicenseKeyProviderInterface::class);
        $provider1->expects($this->once())->method('provide')->willReturn($key);

        $provider2 = $this->createMock(LicenseKeyProviderInterface::class);
        $provider2->expects($this->never())->method('provide');

        $sut = new CompositeLicenseKeyProvider($provider1, $provider2);
        $this->assertEquals($key, $sut->provide());
    }

    public function testProvideDoesReturnFirstProviderWithResult()
    {
        $key = '35660a21-b857-4d9c-aed9-9ad5974bda96';
        $provider1 = $this->createMock(LicenseKeyProviderInterface::class);
        $provider1->expects($this->once())->method('provide')->willReturn(null);

        $provider2 = $this->createMock(LicenseKeyProviderInterface::class);
        $provider2->expects($this->once())->method('provide')->willReturn($key);

        $sut = new CompositeLicenseKeyProvider($provider1, $provider2);
        $this->assertEquals($key, $sut->provide());
    }
}
