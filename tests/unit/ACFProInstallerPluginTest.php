<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Util\RemoteFilesystem;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\ACFProInstallerPlugin;
use PivvenIT\Composer\Installers\ACFPro\Download\DownloadMatcherInterface;
use PivvenIT\Composer\Installers\ACFPro\Download\RewriteUrlRemoteFilesystem;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Appenders\UrlLicenseKeyAppenderInterface;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\LicenseKeyProviderFactoryInterface;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\LicenseKeyProviderInterface;

class ACFProInstallerPluginTest extends TestCase
{
    public function testImplementsPluginInterface()
    {
        $this->assertInstanceOf(PluginInterface::class, new ACFProInstallerPlugin());
    }

    public function testImplementsEventSubscriberInterface()
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, new ACFProInstallerPlugin());
    }

    public function testSubscribesToPreFileDownloadEvent()
    {
        $subscribedEvents = ACFProInstallerPlugin::getSubscribedEvents();
        $this->assertEquals(
            $subscribedEvents[PluginEvents::PRE_FILE_DOWNLOAD],
            'onPreFileDownload'
        );
    }

    public function testOnPreFileDownloadWithNonACFUrlDoesNotRewriteUrl()
    {
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0', '>=')) {
            $this->markTestSkipped("This test tests the legacy integration with Composer API V1");
        }
        $event = $this->createMock(PreFileDownloadEvent::class);
        $event->expects($this->never())->method('setRemoteFilesystem');
        $event->method('getProcessedUrl')->willReturn('https://example.com');

        $sut = new ACFProInstallerPlugin();
        $sut->onPreFileDownload($event);
    }

    public function testOnPreFileDownloadWithoutLicenseKeyThrowsException()
    {
        $event = $this->createMock(PreFileDownloadEvent::class);
        $event->method('getProcessedUrl')->willReturn('https://example.com');

        $downloadMatcher = $this->createMock(DownloadMatcherInterface::class);
        $downloadMatcher->method('matches')->willReturn(true);

        $licenseKeyProvider = $this->createMock(LicenseKeyProviderInterface::class);
        $licenseKeyProvider->expects($this->once())->method('provide')->willReturn(null);
        $licenseKeyProviderFactory = $this->createMock(LicenseKeyProviderFactoryInterface::class);
        $licenseKeyProviderFactory->expects($this->once())->method("build")->willReturn($licenseKeyProvider);

        $composer = $this->createMock(Composer::class);
        $composer->method('getConfig')->willReturn(new Config());
        $io = $this->createMock(IOInterface::class);

        $sut = new ACFProInstallerPlugin($licenseKeyProviderFactory, null, $downloadMatcher);
        $sut->activate($composer, $io);
        $this->expectException(MissingKeyException::class);
        $sut->onPreFileDownload($event);
    }

    public function testOnPreFileDownloadWithACFUrlDoesSetRemoteFileSystemWithCorrectURlWithLicenseKey()
    {
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0', '>=')) {
            $this->markTestSkipped("This test tests the legacy integration with Composer API V1");
        }
        $downloadMatcher = $this->createMock(DownloadMatcherInterface::class);
        $downloadMatcher->method('matches')->willReturn(true);

        $licenseKeyProvider = $this->createMock(LicenseKeyProviderInterface::class);
        $key = "ecb0254b-61e1-4132-b511-b78ec5057ed6";
        $url = 'https://example.com/download?v=5.8.7';
        $licenseKeyProvider->expects($this->once())->method('provide')->willReturn($key);
        $licenseKeyProviderFactory = $this->createMock(LicenseKeyProviderFactoryInterface::class);
        $licenseKeyProviderFactory->expects($this->once())->method("build")->willReturn($licenseKeyProvider);

        $licenseKeyAppender = $this->createMock(UrlLicenseKeyAppenderInterface::class);
        $licenseKeyAppender->expects($this->once())->method('append')->withConsecutive([
            $url,
            $key
        ])->willReturn("{$url}&k={$key}");

        $composer = $this->createMock(Composer::class);
        $composer->method('getConfig')->willReturn(new Config());
        $io = $this->createMock(IOInterface::class);

        $rfs = $this->createMock(RemoteFilesystem::class);
        $rfs->expects($this->once())->method('getOptions')->willReturn([]);
        $rfs->expects($this->once())->method('isTlsDisabled')->willReturn(true);

        $event = $this->createMock(PreFileDownloadEvent::class);
        $event->expects($this->once())->method('setRemoteFilesystem');
        $event->method('getProcessedUrl')->willReturn($url);
        $event->method('getRemoteFileSystem')->willReturn($rfs);
        $event->expects($this->once())->method('setRemoteFileSystem')
            ->with($this->isInstanceOf(RewriteUrlRemoteFilesystem::class));

        $sut = new ACFProInstallerPlugin($licenseKeyProviderFactory, $licenseKeyAppender, $downloadMatcher);
        $sut->activate($composer, $io);
        $sut->onPreFileDownload($event);
    }
}
