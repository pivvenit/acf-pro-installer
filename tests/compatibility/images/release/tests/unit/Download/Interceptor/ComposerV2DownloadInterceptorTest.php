<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor;

use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\ComposerV2DownloadInterceptor;

class ComposerV2DownloadInterceptorTest extends TestCase
{
    public function testComposerV1DownloadInterceptorReplacesTheFilesystem()
    {
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0', '<')) {
            $this->markTestSkipped("This test tests the integration with Composer API V2");
        }

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
