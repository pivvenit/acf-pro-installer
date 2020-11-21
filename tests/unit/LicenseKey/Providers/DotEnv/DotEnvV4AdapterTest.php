<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\LicenseKey\Providers\DotEnv;

use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvV4Adapter;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\EnvironmentVariableLicenseKeyProvider;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DotEnvV4AdapterTest extends TestCase
{
    /**
     * @var callable
     */
    private $autoloader;

    public function testLoadWithKeyInEnvFileMakesItAvailable()
    {
        $key = "ab83a014-61f5-412b-9084-5c5b056105c0";
        $this->autoloader = function ($className) {
            if ($className == "Dotenv\\Dotenv") {
                $mock = new class {
                    public static function createImmutable()
                    {
                        return new self;
                    }

                    public static function safeLoad()
                    {
                        // Load the ACF_PRO_KEY with the key specified above
                        putenv(
                            sprintf(
                                "%s=%s",
                                EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME,
                                "ab83a014-61f5-412b-9084-5c5b056105c0"
                            )
                        );
                    }
                };
                class_alias(get_class($mock), 'Dotenv\\Dotenv');
            }
        };
        spl_autoload_register($this->autoloader, true, true);
        $sut = new DotEnvV4Adapter();
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
        $sut->load(getcwd());
        $this->assertEquals($key, getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
    }

    public function testLoadWithoutKeyInEnvFileDoesNotSetKey()
    {
        $this->autoloader = function ($className) {
            if ($className == "Dotenv\\Dotenv") {
                $mock = new class {
                    public static function createImmutable()
                    {
                        return new self;
                    }

                    public static function safeLoad()
                    {
                        // Does not load anything
                        return;
                    }
                };
                class_alias(get_class($mock), 'Dotenv\\Dotenv');
            }
        };
        spl_autoload_register($this->autoloader, true, true);
        $sut = new DotEnvV4Adapter();
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
        $sut->load(getcwd());
        $this->assertFalse(getenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->autoloader != null) {
            spl_autoload_unregister($this->autoloader);
        }
        putenv(EnvironmentVariableLicenseKeyProvider::ENV_VARIABLE_NAME); //Clears the environment variable
    }
}
