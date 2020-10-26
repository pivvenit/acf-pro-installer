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
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\BackwardsCompatibleDownloadInterceptorFactory;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\DownloadInterceptorInterface;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Appenders\UrlLicenseKeyAppenderInterface;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\LicenseKeyProviderFactoryInterface;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\LicenseKeyProviderInterface;
use PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor\BackwardsCompatibleDownloadInterceptorFactoryTest;

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

    public function testDeactivateDoesNothing()
    {
        $sut = new ACFProInstallerPlugin();
        $composer = $this->createMock(Composer::class);
        $io = $this->createMock(IOInterface::class);
        $sut->activate($composer, $io);
        $composer->expects($this->never())->method($this->anything());
        $io->expects($this->never())->method($this->anything());
        $sut->deactivate($composer, $io);
    }

    public function testUninstallDoesNothing()
    {
        $sut = new ACFProInstallerPlugin();
        $composer = $this->createMock(Composer::class);
        $io = $this->createMock(IOInterface::class);
        $sut->activate($composer, $io);
        $composer->expects($this->never())->method($this->anything());
        $io->expects($this->never())->method($this->anything());
        $sut->uninstall($composer, $io);
    }

    public function testOnPreFileDownloadWithNonACFUrlDoesNotCreateInterceptor()
    {
        $event = $this->createMock(PreFileDownloadEvent::class);
        $event->expects($this->once())->method('getProcessedUrl')->willReturn('http://example.com');

        $downloadInterceptorFactory = $this->createMock(BackwardsCompatibleDownloadInterceptorFactory::class);
        $sut = new ACFProInstallerPlugin(null, null, null, $downloadInterceptorFactory);
        $downloadInterceptorFactory->expects($this->never())->method('build');
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

    public function testOnPreFileDownloadWithACFUrlDoesCreateInterceptorAndInterceptsRequest()
    {
        $event = $this->createMock(PreFileDownloadEvent::class);
        $url = 'https://example.com/download?v=5.8.7';
        $key = '1234';
        $newUrl = "{$url}&k={$key}";
        $event->expects($this->once())->method('getProcessedUrl')->willReturn($url);

        $licenseKeyProvider = $this->createMock(LicenseKeyProviderInterface::class);
        $licenseKeyProvider->expects($this->once())->method('provide')->willReturn($key);

        $licenseKeyProviderFactory = $this->createMock(LicenseKeyProviderFactoryInterface::class);
        $licenseKeyProviderFactory->expects($this->once())->method('build')->willReturn($licenseKeyProvider);

        $licenseKeyAppender = $this->createMock(UrlLicenseKeyAppenderInterface::class);
        $licenseKeyAppender->expects($this->once())->method('append')->willReturn($newUrl);

        $downloadMatcher = $this->createMock(DownloadMatcherInterface::class);
        $downloadMatcher->expects($this->once())->method('matches')->with($url)->willReturn(true);

        $downloadInterceptor = $this->createMock(DownloadInterceptorInterface::class);
        $downloadInterceptor->expects($this->once())->method('intercept')->with($event, $newUrl);

        $downloadInterceptorFactory = $this->createMock(BackwardsCompatibleDownloadInterceptorFactory::class);
        $downloadInterceptorFactory->expects($this->once())->method('build')->willReturn($downloadInterceptor);

        $sut = new ACFProInstallerPlugin(
            $licenseKeyProviderFactory,
            $licenseKeyAppender,
            $downloadMatcher,
            $downloadInterceptorFactory
        );
        $sut->activate($this->createMock(Composer::class), $this->createMock(IOInterface::class));
        $sut->onPreFileDownload($event);
    }
}
