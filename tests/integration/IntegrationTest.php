<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test\Integration;

use PHPUnit\Framework\TestCase;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use Symfony\Component\Process\Process;

class IntegrationTest extends TestCase
{
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

        // Build the app image
        $process = new Process(
            [
                "docker",
                "build",
                "-t",
                "acf-pro-installer/testapp:latest",
                "."
            ],
            __DIR__ . "/images/app"
        );
        $process->mustRun();

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

        $process = new Process(["docker", "network", "create", "--driver", "bridge", "acf-pro-installer-test"]);
        $process->mustRun();

        // start registry image
        $process = new Process(
            [
                "docker",
                "run",
                "-d",
                "--network=acf-pro-installer-test",
                "--network-alias=connect.advancedcustomfields.com",
                "--name",
                "acf-pro-installer-registry",
                "--rm",
                "acf-pro-installer/registry:latest"
            ]
        );
        $process->mustRun();
    }

    public static function tearDownAfterClass(): void
    {
        $process = new Process(["docker", "rm", "-f", "acf-pro-installer-registry"]);
        $process->mustRun();
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

    /**
     * @return string
     */
    private static function getReleaseDir(): string
    {
        return __DIR__ . "/images/app/release/src";
    }

    public function testWithSpecificVersionInstallWorksCorrectly()
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
                "ACF_PRO_KEY=test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:latest"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    public function testWithDevMasterInstallWorksCorrectly()
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
                "ACF_PRO_KEY=test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:latest"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    public function testWithDevMasterAndDotEnvV3InstallWorksCorrectly()
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
                "ACF_PRO_KEY=test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:latest"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    public function testWithDevMasterAndDotEnvV4InstallWorksCorrectly()
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
                "ACF_PRO_KEY=test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:latest"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    public function testWithComposerConfigKeyWorksCorrectly()
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.dev-master.json";
        $makeConfigDirCommand = 'mkdir ~/.composer';
        $configCommand = 'echo "{ \\"config\\": { \\"acf-pro-key\\": \\"test\\"}}" >> ~/.composer/config.json';
        $installCommand =  "composer install --no-dev --no-scripts --no-progress --no-suggest";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:latest",
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

    public function testWithDotEnvV5EnvFileWorksCorrectly()
    {
        $localComposerPath = __DIR__ . "/scenarios/composer.dotenv5.json";
        $localDotEnvFilePath = __DIR__."/scenarios/dotenv5.env";
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-test",
                "-e",
                "ACF_PRO_KEY=test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "-v",
                "{$localDotEnvFilePath}:/app/.env",
                "acf-pro-installer/testapp:latest"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    public function testWithBedrockInstallWorksCorrectly()
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
                "ACF_PRO_KEY=test",
                "-v",
                "{$localComposerPath}:/app/composer.json",
                "acf-pro-installer/testapp:latest"
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
