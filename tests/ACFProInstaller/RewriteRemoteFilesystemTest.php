<?php namespace PhilippBaschke\ACFProInstaller\Test;

use Composer\Util\RemoteFilesystem;
use PhilippBaschke\ACFProInstaller\RewriteUrlRemoteFilesystem;
use PHPUnit\Framework\TestCase;

class RewriteRemoteFilesystemTest extends TestCase
{
    protected $io;
    protected $config;

    protected function setUp() : void
    {
        $this->io = $this->createMock('Composer\IO\IOInterface');
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
