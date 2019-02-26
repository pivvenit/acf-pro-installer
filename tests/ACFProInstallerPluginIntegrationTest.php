<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test;

use Composer\Console\Application;
use Composer\Util\Filesystem;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\ACFProInstallerPlugin;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;
use Symfony\Component\Console\Input\ArrayInput;

class ACFProInstallerPluginIntegrationTest extends TestCase
{
    /**
     * @var string $testPath
     */
    private $testPath;

    /**
     * @var Filesystem $fs
     */
    private $fs;

    public static function setUpBeforeClass(): void
    {
        if (file_exists(getcwd() . DIRECTORY_SEPARATOR . '.env')) {
            $dotenv = Dotenv::create(getcwd());
            $dotenv->load();
        }
        $key = getenv(ACFProInstallerPlugin::KEY_ENV_VARIABLE);
        if (empty($key)) {
            throw new MissingKeyException();
        }
    }

    public static function tearDownAfterClass(): void
    {
        // no operation
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fs = new Filesystem();
        $testId = uniqid("acf-pro-installer-test");
        $this->testPath = sys_get_temp_dir() . "/{$testId}";
        $this->fs->ensureDirectoryExists($this->testPath);
        ini_set('memory_limit', '512M');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fs->removeDirectory($this->testPath);
    }

    public function testWithSpecificVersionInstallWorksCorrectly()
    {
        $this->createComposerJson("5.7.10");
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
        $this->assertSame(0, $application->run($input));
    }

    public function testWithDevMasterInstallWorksCorrectly()
    {
        $this->createComposerJson("dev-master");
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
        $this->assertSame(0, $application->run($input));
    }

    private function createComposerJson(string $version)
    {
        $pluginDir = realpath(__DIR__ . "/../");
        $data = (object)[
            "name" => "test/plugintest",
            "repositories" => [
                (object)[
                    "type" => "path",
                    "url" => $pluginDir,
                    "version" => "dev-master",
                    "options" => (object)[
                        "symlink" => false
                    ]
                ],
                (object)[
                    "type" => "composer",
                    "url" => "https://pivvenit.github.io/acf-composer-bridge/composer/v1/"
                ]
            ],
            "minimum-stability" => "dev",
            "require" => (object)[
                "pivvenit/acf-pro-installer" => "dev-master",
                "advanced-custom-fields/advanced-custom-fields-pro" => "{$version}"
            ]
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($this->testPath . "/composer.json", $json);
    }
}
