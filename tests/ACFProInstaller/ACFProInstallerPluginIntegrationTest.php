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
        if (file_exists(getcwd() . DIRECTORY_SEPARATOR . '.env')) {
            $dotenv = Dotenv::create(getcwd());
            $dotenv->load();
        }
        $key = getenv(ACFProInstallerPlugin::KEY_ENV_VARIABLE);
        if (!$key) {
            throw new MissingKeyException();
        }
        parent::setUp();
        $this->fs = new Filesystem();
        $testId = uniqid("acf-pro-installer-test");
        $this->testPath = sys_get_temp_dir() . "/{$testId}";
        $this->fs->ensureDirectoryExists($this->testPath);
        $this->createComposerJson();
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

    public function test_Install_WorksCorrectly()
    {
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
        $this->assertSame(0, $application->run($input));
    }

    private function createComposerJson()
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
                    "url" => "https://pivvenit.github.io/acf-composer-bridge/v1/"
                ]
            ],
            "minimum-stability" => "dev",
            "require" => (object)[
                "pivvenit/acf-pro-installer" => "dev-master",
                "advanced-custom-fields/advanced-custom-fields-pro" => "5.7.10"
            ]
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($this->testPath . "/composer.json", $json);
    }
}
