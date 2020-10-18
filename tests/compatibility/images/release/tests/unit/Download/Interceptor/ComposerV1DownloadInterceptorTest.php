<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor;

use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Util\RemoteFilesystem;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\ComposerV1DownloadInterceptor;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\RewriteUrlRemoteFilesystem;

class ComposerV1DownloadInterceptorTest extends TestCase
{
    public function testComposerV1DownloadInterceptorReplacesTheFilesystem()
    {
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0', '>=')) {
            $this->markTestSkipped("This test tests the legacy integration with Composer API V1");
        }

        $url = 'https://example.com/download?v=5.8.7';
        $newUrl = "{$url}&k=ecb0254b-61e1-4132-b511-b78ec5057ed6";

        $rfs = $this->createMock(RemoteFilesystem::class);
        $rfs->expects($this->once())->method('getOptions')->willReturn([]);
        $rfs->expects($this->once())->method('isTlsDisabled')->willReturn(true);

        $event = $this->createMock(PreFileDownloadEvent::class);
        $event->expects($this->once())->method('setRemoteFilesystem');
        $event->method('getProcessedUrl')->willReturn($url);
        $event->method('getRemoteFileSystem')->willReturn($rfs);
        $event->expects($this->once())->method('setRemoteFileSystem')
            ->with($this->isInstanceOf(RewriteUrlRemoteFilesystem::class));

        $sut = new ComposerV1DownloadInterceptor();
        $sut->intercept($event, $newUrl);
    }
}
