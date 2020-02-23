<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\LicenseKeyProviders\DotEnv;

use Composer\Util\Filesystem;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvAdapterFactory;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvV4Adapter;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\EnvironmentVariableLicenseKeyProvider;

class DotEnvV3AdapterTest extends TestCase
{
    /**
     * @var string
     */
    private $testPath;
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (DotEnvAdapterFactory::isV4()) {
            $this->markTestSkipped(
                'This test run is performed using vlucas/phpdotenv ^4.0, skipping testing the ^3.0 tests'
            );
            return;
        }
        $this->fs = new Filesystem();
        $this->testPath = sprintf("%s%s%s", sys_get_temp_dir(), DIRECTORY_SEPARATOR, uniqid("acf-pro-installer-test"));
        $this->fs->ensureDirectoryExists($this->testPath);
    }

    public function testLoadWithKeyInEnvFileMakesItAvailable()
    {
        $key = "ab83a014-61f5-412b-9084-5c5b056105c0";
        file_put_contents(
            sprintf("%s%s.env", $this->testPath, DIRECTORY_SEPARATOR),
            sprintf("%s=%s", EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME, $key)
        );
        $sut = new DotEnvV4Adapter();
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
        $sut->load($this->testPath);
        $this->assertEquals($key, getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
    }

    public function testLoadWithoutKeyInEnvFileDoesNotSetKey()
    {
        file_put_contents(
            sprintf("%s%s.env", $this->testPath, DIRECTORY_SEPARATOR),
            ""
        );
        $sut = new DotEnvV4Adapter();
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
        $sut->load($this->testPath);
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
    }

    public function testLoadWithoutEnvFileDoesNotSetKey()
    {
        $sut = new DotEnvV4Adapter();
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
        $sut->load($this->testPath);
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fs->removeDirectory($this->testPath);
        putenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME); //Clears the environment variable
    }
}
