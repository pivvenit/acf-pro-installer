<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test\Integration;

use PHPUnit\Framework\TestCase;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use Symfony\Component\Process\Process;

class IntegrationTest extends TestCase
{
    public static $phpVersions = [
        "7.4" => "7.4-alpine@sha256:253e5534cff41e167895a1f749cbc557ea673e48429589cc9df2e896fe99958e"
    ];

    public static $composerVersions = [
        "1.10" => "1.10@sha256:5821c81e84f77906e6ae8c9a2d016d4d635669ca595b12e949fcea518d6ed415",
        "2.0" => "2.0@sha256:a70eecbeeddbb9d06e0897e6d00bbf32388dba87aa4cc15e107e58367e878be1",
    ];

    public function getTestMatrix()
    {
        foreach (array_keys(self::$phpVersions) as $phpVersion) {
            foreach (array_keys(self::$composerVersions) as $composerVersion) {
                yield "PHP {$phpVersion}, Composer {$composerVersion}" => [$phpVersion, $composerVersion];
            }
        }
    }

    public static function setUpBeforeClass(): void
    {
        // Only run these tests if docker is available
        $process = new Process(['docker', '-v']);
        $process->run();
        if (!$process->isSuccessful()) {
            self::markTestSkipped("Unable to find docker daemon");
            return;
        }
        // Copy the current source code to the release folder inside the build context
        $releaseDir = self::getReleaseDir();
        @mkdir($releaseDir, 0777, true);
        copy(__DIR__ . "/../../composer.json", $releaseDir . "/../composer.json");

        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__ . "/../../src", RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item
        ) {
            if ($item->isDir()) {
                @mkdir($releaseDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $releaseDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }


        // Build the registry image
        $process = new Process(
            [
                "docker",
                "build",
                "-t",
                "acf-pro-installer/registry:latest",
                "."
            ],
            __DIR__ . "/images/registry"
        );
        $process->mustRun();

        // If the network exists, remove it first
        $process = new Process(["docker", "network", "ls", "-f", "name=acf-pro-installer-test"]);
        $process->mustRun();
        if (strstr($process->getOutput(), 'acf-pro-installer-test') !== false) {
            $process = new Process(["docker", "network", "rm", "acf-pro-installer-test"]);
            $process->mustRun();
        }

        $process = new Process(["docker", "network", "create", "--driver", "bridge", "acf-pro-installer-test"]);
        $process->mustRun();

        foreach (self::$phpVersions as $shortPhpVersion => $fullPhpVersion) {
            foreach (self::$composerVersions as $shortComposerVersion => $fullComposerVersion) {
                // Build the app image
                $process = new Process(
                    [
                        "docker",
                        "build",
                        "--build-arg",
                        "PHP_VERSION={$fullPhpVersion}",
                        "--build-arg",
                        "COMPOSER_VERSION={$fullComposerVersion}",
                        "-t",
                        "acf-pro-installer/testapp:{$shortPhpVersion}-{$shortComposerVersion}",
                        "."
                    ],
                    __DIR__ . "/images/app"
                );
                $process->mustRun();
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        $process = new Process(["docker", "network", "rm", "acf-pro-installer-test"]);
        $process->mustRun();

        parent::tearDownAfterClass();
        $releaseDir = self::getReleaseDir();
        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($releaseDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $item
        ) {
            if ($item->isDir()) {
                rmdir($releaseDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                unlink($releaseDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
        unlink($releaseDir . "/../composer.json");
        rmdir($releaseDir);
    }

    public function setUp(): void
    {
        parent::setUp();
        // start registry image
        $registry = new Process(
            [
                "docker",
                "run",
                "-d",
                "-i",
                "--network=acf-pro-installer-test",
                "--network-alias=connect.advancedcustomfields.com",
                "--name",
                "acf-pro-installer-registry",
                "--rm",
                "acf-pro-installer/registry:latest"
            ]
        );
        $registry->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $registry = new Process(["docker", "logs", "acf-pro-installer-registry"]);
        $registry->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $process = new Process(["docker", "rm", "-f", "acf-pro-installer-registry"]);
        $process->mustRun();
    }

    /**
     * @return string
     */
    private static function getReleaseDir(): string
    {
        return __DIR__ . "/images/app/release/src";
    }

    /**
     * @dataProvider getTestMatrix
     */
    public function testWithSpecificVersionInstallWorksCorrectly($phpVersion, $composerVersion)
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.specific-version.json";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-e",
                "ACF_PRO_KEY=938C927AFC694954A84476CF3CBD28B3",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:{$phpVersion}-{$composerVersion}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    /**
     * @dataProvider getTestMatrix
     */
    public function testWithDevMasterInstallWorksCorrectly($phpVersion, $composerVersion)
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.dev-master.json";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-e",
                "ACF_PRO_KEY=938C927AFC694954A84476CF3CBD28B3",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:{$phpVersion}-{$composerVersion}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    /**
     * @dataProvider getTestMatrix
     */
    public function testWithDevMasterAndDotEnvV3InstallWorksCorrectly($phpVersion, $composerVersion)
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.dotenv3.json";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-e",
                "ACF_PRO_KEY=938C927AFC694954A84476CF3CBD28B3",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:{$phpVersion}-{$composerVersion}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    /**
     * @dataProvider getTestMatrix
     */
    public function testWithDevMasterAndDotEnvV4InstallWorksCorrectly($phpVersion, $composerVersion)
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.dotenv4.json";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-e",
                "ACF_PRO_KEY=938C927AFC694954A84476CF3CBD28B3",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:{$phpVersion}-{$composerVersion}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    /**
     * @dataProvider getTestMatrix
     */
    public function testWithComposerConfigKeyWorksCorrectly($phpVersion, $composerVersion)
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.dev-master.json";
        $makeConfigDirCommand = 'mkdir ~/.composer';
        $configCommand = 'echo "{ \\"config\\": { \\"acf-pro-key\\": \\"938C927AFC694954A84476CF3CBD28B3\\"}}" >> ~/.composer/config.json';
        $installCommand = "composer install --no-dev --no-scripts --no-progress --no-suggest";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:{$phpVersion}-{$composerVersion}",
                "/bin/sh",
                "-c",
                "{$makeConfigDirCommand};{$configCommand};{$installCommand}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    /**
     * @dataProvider getTestMatrix
     */
    public function testWithDotEnvV5EnvFileWorksCorrectly($phpVersion, $composerVersion)
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.dotenv5.json";
        $localDotEnvFilePath = __DIR__ . "/scenarios/dotenv5.env";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-e",
                "ACF_PRO_KEY=938C927AFC694954A84476CF3CBD28B3",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "-v",
                "{$localDotEnvFilePath}:/app/.env",
                "acf-pro-installer/testapp:{$phpVersion}-{$composerVersion}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    /**
     * @dataProvider getTestMatrix
     */
    public function testWithBedrockInstallWorksCorrectly($phpVersion, $composerVersion)
    {
        // Download latest bedrock composer file and modify it to contain the required repository
        $composerJsonPath = __DIR__ . "/scenarios/composer.bedrock.json";
        $json = file_get_contents("https://raw.githubusercontent.com/roots/bedrock/master/composer.json");
        $composerData = json_decode($json);
        array_unshift(
            $composerData->repositories,
            (object)[
                "type" => "path",
                "url" => "/plugin",
                "version" => "dev-master"
            ],
            (object)[
                "type" => "composer",
                "url" => "file:///registry/packages.json"
            ]
        );
        $composerData->require->{"pivvenit/acf-pro-installer"} = "dev-master";
        $composerData->require->{"advanced-custom-fields/advanced-custom-fields-pro"} = "dev-master";
        file_put_contents($composerJsonPath, json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $localComposerPath = __DIR__ . "/scenarios/composer.bedrock.json";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-e",
                "ACF_PRO_KEY=938C927AFC694954A84476CF3CBD28B3",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:{$phpVersion}-{$composerVersion}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }
}
