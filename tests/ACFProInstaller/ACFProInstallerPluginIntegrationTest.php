<?php

namespace PhilippBaschke\ACFProInstaller\Test;


use Composer\Console\Application;
use Composer\Util\Filesystem;
use Dotenv\Dotenv;
use PhilippBaschke\ACFProInstaller\ACFProInstallerPlugin;
use PhilippBaschke\ACFProInstaller\Exceptions\MissingKeyException;
use PHPUnit\Framework\TestCase;
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if (file_exists(__DIR__.DIRECTORY_SEPARATOR . '.env')) {
            $dotenv = Dotenv::create(__DIR__);
            $dotenv->load();
        }
        $key = getenv(ACFProInstallerPlugin::KEY_ENV_VARIABLE);
        if (empty($key)) {
            throw new MissingKeyException();
        }
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

    public function test_WithSpecificVersion_Install_WorksCorrectly()
    {
        $this->createComposerJson("5.7.10");
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
        $this->assertSame(0, $application->run($input));
    }

    public function test_WithDevMaster_Install_WorksCorrectly()
    {
        $this->createComposerJson("dev-master");
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
        $this->assertSame(0, $application->run($input));
    }

    private function createComposerJson(string $version)
    {
        $pluginDir = realpath(__DIR__ . "/../../");
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
