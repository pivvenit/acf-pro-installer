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
        if (file_exists(getcwd() . DIRECTORY_SEPARATOR . '.env')) {
            if (method_exists(Dotenv::class, 'createImmutable')) {
                // vlucas/phpdotenv ^4.0
                $dotenv = Dotenv::createImmutable(getcwd());
            } else {
                // vlucas/phpdotenv ^3.0
                $dotenv = Dotenv::create(getcwd());
            }
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

    public function testWithDevMasterInstallWithDotEnv3WorksCorrectly()
    {
        $this->createComposerJson("dev-master", "^3.0");
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false);
        $this->assertSame(0, $application->run($input));
    }

    public function testWithDevMasterInstallWithDotEnv4WorksCorrectly()
    {
        $this->createComposerJson("dev-master", "^4.0");
        $input = new ArrayInput(['command' => 'install', "--working-dir" => $this->testPath]);
        $application = new Application();
        $application->setAutoExit(false);
        $this->assertSame(0, $application->run($input));
    }

    public function testWithBedrockInstallWorksCorrectly()
    {
        ini_set('memory_limit', '2G');
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
        $devName = getenv('TRAVIS_BRANCH') ?? 'master';
        array_unshift($composerData->repositories, (object)[
            "type" => "vcs",
            "url" => $pluginDir,
            "options" => (object)[
                "symlink" => false
            ]
        ]);
        $composerData->require->{"pivvenit/acf-pro-installer"} = "dev-{$devName} as 2.999.0";
        $composerData->repositories[] = (object)[
            "type" => "composer",
            "url" => "https://pivvenit.github.io/acf-composer-bridge/composer/v3/"
        ];
        file_put_contents($composerJsonPath, json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function createComposerJson(string $version, string $dotEnvVersion = null)
    {
        $pluginDir = $this->getPluginDirectory();
        $devName = getenv('TRAVIS_BRANCH') ?? 'master';
        $deps = [
            "pivvenit/acf-pro-installer" => "dev-{$devName} as 2.999.0",
            "advanced-custom-fields/advanced-custom-fields-pro" => "{$version}"
        ];
        if (!is_null($dotEnvVersion)) {
            $deps['vlucas/phpdotenv'] = $dotEnvVersion;
        }

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
            "require" => (object)$deps
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
