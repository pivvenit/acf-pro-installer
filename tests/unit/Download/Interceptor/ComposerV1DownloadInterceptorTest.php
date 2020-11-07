<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Util\RemoteFilesystem;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\ComposerV1DownloadInterceptor;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\RewriteUrlRemoteFilesystem;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ComposerV1DownloadInterceptorTest extends TestCase
{
    public function testComposerV1DownloadInterceptorReplacesTheFilesystem()
    {
        $url = 'https://example.com/download?v=5.8.7';
        $autoloader = function ($className) {
            if ($className == "Composer\\Plugin\\PreFileDownloadEvent") {
                $mock = new class {
                    private $filesystem;

                    public function setRemoteFilesystem(RemoteFilesystem $filesystem)
                    {
                        $this->filesystem = $filesystem;
                    }

                    public function getRemoteFilesystem()
                    {
                        return $this->filesystem;
                    }

                    public function getProcessedUrl()
                    {
                        return 'https://example.com/download?v=5.8.7';
                    }
                };
                class_alias(get_class($mock), 'Composer\\Plugin\\PreFileDownloadEvent');
            }
        };
        spl_autoload_register($autoloader, true, true);

        $newUrl = "{$url}&k=ecb0254b-61e1-4132-b511-b78ec5057ed6";

        $rfs = $this->createMock(RemoteFilesystem::class);
        $rfs->expects($this->once())->method('getOptions')->willReturn([]);
        $rfs->expects($this->once())->method('isTlsDisabled')->willReturn(true);

        $event = $this->createMock(PreFileDownloadEvent::class);
        $event->expects($this->once())->method('setRemoteFilesystem');
        $event->method('getProcessedUrl')->willReturn($url);
        $event->method('getRemoteFilesystem')->willReturn($rfs);
        $event->expects($this->once())->method('setRemoteFilesystem')
            ->with($this->isInstanceOf(RewriteUrlRemoteFilesystem::class));

        $composer = $this->createMock(Composer::class);
        $composer->method('getConfig')->willReturn(new Config());
        $sut = new ComposerV1DownloadInterceptor($composer, $this->createMock(IOInterface::class));
        $sut->intercept($event, $newUrl);
    }
}
