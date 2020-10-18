<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\Compatibility;

use PHPUnit\Framework\TestCase;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use Symfony\Component\Process\Process;

class CompatibilityTest extends TestCase
{
    public static $phpVersions = [
        "7.4" => "7.4-alpine@sha256:253e5534cff41e167895a1f749cbc557ea673e48429589cc9df2e896fe99958e"
    ];

    public static $composerVersions = [
        "1.10" => "1.10@sha256:5821c81e84f77906e6ae8c9a2d016d4d635669ca595b12e949fcea518d6ed415",
        "2.0" => "2.0@sha256:3a93c5674bce0938ba2166f23b27e54378da552bd19986e4ad80aabc73a467e6",
    ];

    public function getPhpVersions()
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
        $testDir = self::getTestDir();
        @mkdir($releaseDir, 0777, true);
        @mkdir($testDir, 0777, true);
        copy(__DIR__ . "/../../composer.json", $releaseDir . "/../composer.json");
        copy(__DIR__ . "/../../phpunit.xml.dist", $releaseDir . "/../phpunit.xml.dist");

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

        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__ . "/../../tests/unit", RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item
        ) {
            if ($item->isDir()) {
                @mkdir($testDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $testDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }

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
                        "acf-pro-installer/compatibility-test:{$shortPhpVersion}-{$shortComposerVersion}",
                        "."
                    ],
                    __DIR__ . "/images"
                );
                $process->mustRun();
            }
        }
        $process = new Process(["docker", "network", "create", "--driver", "bridge", "acf-pro-installer-compat-test"]);
        $process->mustRun();
    }

    /**
     * @dataProvider getPhpVersions
     */
    public function test($phpVersion, $composerVersion)
    {
        $process = new Process(
            [
                "docker",
                "run",
                "--rm",
                "-i",
                "--network=acf-pro-installer-compat-test",
                "acf-pro-installer/compatibility-test:{$phpVersion}-{$composerVersion}"
            ],
            __DIR__
        );
        $process->setTimeout(60);
        $process->mustRun(function ($type, $buffer) {
            echo $buffer;
        });
        $this->assertEquals(0, $process->getExitCode());
    }

    public static function tearDownAfterClass(): void
    {
        $process = new Process(["docker", "network", "rm", "acf-pro-installer-compat-test"]);
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
        return __DIR__ . "/images/release/src";
    }

    /**
     * @return string
     */
    private static function getTestDir(): string
    {
        return __DIR__ . "/images/release/tests/unit";
    }
}
