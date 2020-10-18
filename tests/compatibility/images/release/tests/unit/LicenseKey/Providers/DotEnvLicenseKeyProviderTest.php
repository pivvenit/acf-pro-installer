<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\LicenseKey\Providers;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvAdapterInterface;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvLicenseKeyProvider;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\EnvironmentVariableLicenseKeyProvider;

class DotEnvLicenseKeyProviderTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        // Clear the environment variable
        putenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Clear the environment variable if the system has one set.
        putenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME);
    }

    public function testProvideCallsLoadOnDotEnvAdapter()
    {
        $dotEnvProvider = $this->createMock(DotEnvAdapterInterface::class);
        $dotEnvProvider->expects($this->once())->method('load');
        $sut = new DotEnvLicenseKeyProvider($dotEnvProvider);
        $this->assertNull($sut->provide());
    }

    public function testProvideOnLoadedEnvironmentvariableReturnsResult()
    {
        $key = "d5bd0094-638f-45a1-b0db-fd0b1d01b453";
        $dotEnvProvider = $this->createMock(DotEnvAdapterInterface::class);
        $dotEnvProvider->expects($this->once())->method('load')->will(
            $this->returnCallback(
                function () use ($key) {
                    putenv(sprintf("%s=%s", EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME, $key));
                }
            )
        );
        $sut = new DotEnvLicenseKeyProvider($dotEnvProvider);
        $this->assertEquals($key, $sut->provide());
    }
}
