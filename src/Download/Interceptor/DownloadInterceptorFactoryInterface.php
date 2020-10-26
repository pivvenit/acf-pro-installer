<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Download\Interceptor;

use Composer\Composer;
use Composer\IO\IOInterface;

interface DownloadInterceptorFactoryInterface
{
    /**
     * Returns the correct DownloadInterceptor given the environment
     *
     * @param string $composerApiVersion
     * @param Composer $composer
     * @param IOInterface $io
     * @return DownloadInterceptorInterface
     */
    public function build(
        string $composerApiVersion,
        Composer $composer,
        IOInterface $io
    ): DownloadInterceptorInterface;
}
