<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor;

use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Util\RemoteFilesystem;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\ComposerV2DownloadInterceptor;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ComposerV2DownloadInterceptorTest extends TestCase
{
    public function testComposerV1DownloadInterceptorReplacesTheFilesystem()
    {
        $autoloader = function ($className) {
            if ($className == "Composer\\Plugin\\PreFileDownloadEvent") {
                $mock = new class {

                    private $url = 'https://example.com/download?v=5.8.7';

                    public function setProcessedUrl(string $url)
                    {
                        $this->url = $url;
                    }

                    public function getProcessedUrl()
                    {
                        return $this->url;
                    }
                };
                class_alias(get_class($mock), 'Composer\\Plugin\\PreFileDownloadEvent');
            }
        };
        spl_autoload_register($autoloader, true, true);

        $url = 'https://example.com/download?v=5.8.7';
        $newUrl = "{$url}&k=ecb0254b-61e1-4132-b511-b78ec5057ed6";

        $event = $this->createMock(PreFileDownloadEvent::class);
        $event->method('getProcessedUrl')->willReturn($url);
        $event->expects($this->once())->method('setProcessedUrl')
            ->with($newUrl);

        $sut = new ComposerV2DownloadInterceptor();
        $sut->intercept($event, $newUrl);
    }
}
