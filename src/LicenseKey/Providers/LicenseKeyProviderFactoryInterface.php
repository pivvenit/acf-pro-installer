<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers;

use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * Builds a LicenseKeyProvider given the environment
 */
interface LicenseKeyProviderFactoryInterface
{
    /**
     * Returns the correct LicenseKeyProvider given the environment
     *
     * @param  Composer    $composer
     * @param  IOInterface $io
     * @return LicenseKeyProviderInterface
     */
    public function build(Composer $composer, IOInterface $io): LicenseKeyProviderInterface;
}
