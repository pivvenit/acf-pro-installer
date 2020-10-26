<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Download\Interceptor;

use Composer\Composer;
use Composer\IO\IOInterface;
use function version_compare;

class BackwardsCompatibleDownloadInterceptorFactory implements DownloadInterceptorFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function build(string $composerApiVersion, Composer $composer, IOInterface $io): DownloadInterceptorInterface
    {
        if (version_compare($composerApiVersion, '2.0', '<')) {
            return new ComposerV1DownloadInterceptor($composer, $io);
        }
        return new ComposerV2DownloadInterceptor();
    }
}
