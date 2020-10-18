<?php

namespace PivvenIT\Composer\Installers\ACFPro\Test\Download\Interceptor;

use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Util\RemoteFilesystem;
use PHPUnit\Framework\TestCase;
use PivvenIT\Composer\Installers\ACFPro\Download\Interceptor\RewriteUrlRemoteFilesystem;

class RewriteRemoteFilesystemTest extends TestCase
{
    protected $io;
    protected $config;

    protected function setUp() : void
    {
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0', '>=')) {
            $this->markTestSkipped("This test tests the legacy integration with Composer API V1");
        }
        $this->io = $this->createMock(IOInterface::class);
    }

    public function testExtendsComposerRemoteFilesystem()
    {
        $this->assertInstanceOf(
            RemoteFilesystem::class,
            new RewriteUrlRemoteFilesystem('', $this->io)
        );
    }

    // Inspired by testCopy of Composer
    public function testCopyUsesRewriteFileUrl()
    {
        $rewriteUrl = 'file://'.__FILE__;
        $rfs = new RewriteUrlRemoteFilesystem($rewriteUrl, $this->io);
        $file = tempnam(sys_get_temp_dir(), 'pb');

        $this->assertTrue(
            $rfs->copy('http://example.org', 'does-not-exist', $file)
        );
        $this->assertFileExists($file);
        unlink($file);
    }
}
