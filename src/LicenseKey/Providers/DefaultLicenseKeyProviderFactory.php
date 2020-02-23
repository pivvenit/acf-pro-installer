<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers;

use Composer\Composer;
use Composer\IO\IOInterface;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvAdapterFactory;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv\DotEnvLicenseKeyProvider;

class DefaultLicenseKeyProviderFactory implements LicenseKeyProviderFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function build(Composer $composer, IOInterface $io): LicenseKeyProviderInterface
    {
        return new CompositeLicenseKeyProvider(
            new DotEnvLicenseKeyProvider(DotEnvAdapterFactory::build()),
            new EnvironmentVariableLicenseKeyProvider(),
            new ComposerConfigLicenseKeyProvider($composer->getConfig())
        );
    }
}
