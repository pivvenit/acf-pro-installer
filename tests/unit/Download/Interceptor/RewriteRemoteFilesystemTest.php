<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor;

use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\RewriteUrlRemoteFilesystem;

class RewriteRemoteFilesystemTest extends TestCase
{
    protected $io;
    protected $config;

    protected function setUp() : void
    {
        $this->io = $this->createMock(IOInterface::class);
        $this->config = $this->createMock(Config::class);
    }

    public function testExtendsComposerRemoteFilesystem()
    {
        $this->assertInstanceOf(
            RemoteFilesystem::class,
            new RewriteUrlRemoteFilesystem('', $this->io, $this->config)
        );
    }

    // Inspired by testCopy of Composer
    public function testCopyUsesRewriteFileUrl()
    {
        $rewriteUrl = 'file://'.__FILE__;
        $rfs = new RewriteUrlRemoteFilesystem($rewriteUrl, $this->io, $this->config);
        $file = tempnam(sys_get_temp_dir(), 'pb');

        $this->assertTrue(
            $rfs->copy('http://example.org', 'does-not-exist', $file)
        );
        $this->assertFileExists($file);
        unlink($file);
    }
}
