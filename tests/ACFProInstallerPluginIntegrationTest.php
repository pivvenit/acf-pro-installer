<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test;

use Composer\Console\Application;
use Composer\Util\Filesystem;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;

class ACFProInstallerPluginIntegrationTest extends TestCase
{
    private const KEY_ENV_VARIABLE = "ACF_PRO_KEY";

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
        ini_set('memory_limit', '1500M');
        if (file_exists(getcwd() . DIRECTORY_SEPARATOR . '.env')) {
            $dotenv = Dotenv::create(getcwd());
            $dotenv->load();
        }
        $key = getenv(self::KEY_ENV_VARIABLE);
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
        $application->setAutoExit(false);
        $this->assertSame(0, $application->run($input));
    }

    public function testWithDevMasterInstallWorksCorrectly()
    {
        $this->createComposerJson("dev-master");
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false);
        $this->assertSame(0, $application->run($input));
    }

    public function testWithBedrockInstallWorksCorrectly()
    {
        $input = new StringInput("create-project roots/bedrock {$this->testPath}");
        $application = new Application();
        $application->setAutoExit(false);
        $application->run($input);

        // Modify the composer file
        $this->appendToComposer();

        $input = new StringInput(
            "--working-dir {$this->testPath} require advanced-custom-fields/advanced-custom-fields-pro"
        );
        $application = new Application();
        $application->setAutoExit(false);
        $this->assertSame(0, $application->run($input));
    }

    private function appendToComposer()
    {
        $pluginDir = $this->getPluginDirectory();
        $composerJsonPath = "{$this->testPath}/composer.json";
        $json = file_get_contents($composerJsonPath);
        $composerData = json_decode($json);
        $composerData->repositories[] = (object)[
            "type" => "vcs",
            "url" => $pluginDir,
            "options" => (object)[
                "symlink" => false
            ]
        ];
        $composerData->repositories[] = (object)[
            "type" => "composer",
            "url" => "https://pivvenit.github.io/acf-composer-bridge/composer/v2/"
        ];
        $composerData->extra->{"branch-alias"} = (object)[
            "dev-master" => "2.0.x-stable"
        ];
        file_put_contents($composerJsonPath, json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function createComposerJson(string $version)
    {
        $pluginDir = $this->getPluginDirectory();
        $data = (object)[
            "name" => "test/plugintest",
            "repositories" => [
                (object)[
                    "type" => "vcs",
                    "url" => $pluginDir,
                    "options" => (object)[
                        "symlink" => false
                    ]
                ],
                (object)[
                    "type" => "composer",
                    "url" => "https://pivvenit.github.io/acf-composer-bridge/composer/v2/"
                ]
            ],
            "minimum-stability" => "dev",
            "require" => (object)[
                "pivvenit/acf-pro-installer" => "dev-master as 2.0.x-dev",
                "advanced-custom-fields/advanced-custom-fields-pro" => "{$version}"
            ]
        ];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($this->testPath . "/composer.json", $json);
    }

    /**
     * @return bool|string
     */
    private function getPluginDirectory()
    {
        $pluginDir = realpath(__DIR__ . "/../");
        return $pluginDir;
    }
}
