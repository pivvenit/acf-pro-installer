<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor;

use Composer\Composer;
use Composer\IO\IOInterface;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\BackwardsCompatibleDownloadInterceptorFactory;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\ComposerV1DownloadInterceptor;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\ComposerV2DownloadInterceptor;

class BackwardsCompatibleDownloadInterceptorFactoryTest extends TestCase
{
    public function testBuildReturnsV1DownloaderForV1()
    {
        $sut = new BackwardsCompatibleDownloadInterceptorFactory();
        $result = $sut->build('1.0.0', $this->createMock(Composer::class), $this->createMock(IOInterface::class));
        $this->assertInstanceOf(ComposerV1DownloadInterceptor::class, $result);
    }

    public function testBuildReturnsV2DownloaderForV2()
    {
        $sut = new BackwardsCompatibleDownloadInterceptorFactory();
        $result = $sut->build('2.0.0', $this->createMock(Composer::class), $this->createMock(IOInterface::class));
        $this->assertInstanceOf(ComposerV2DownloadInterceptor::class, $result);
    }
}
