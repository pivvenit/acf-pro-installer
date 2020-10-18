<?php


namespace PivvenIT\Composer\Installers\ACFPro\Download\Interceptor;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PreFileDownloadEvent;

class ComposerV1DownloadInterceptor implements DownloadInterceptorInterface
{
    /**
     * @var Composer
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function intercept(PreFileDownloadEvent $event, string $modifiedDownloadUrl): void
    {
        $remoteFilesystem = $event->getRemoteFilesystem();
        $event->setRemoteFilesystem(
            new RewriteUrlRemoteFilesystem(
                $modifiedDownloadUrl,
                $this->io,
                $this->composer->getConfig(),
                $remoteFilesystem->getOptions(),
                $remoteFilesystem->isTlsDisabled()
            )
        );
    }
}
