<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv;

use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\EnvironmentVariableLicenseKeyProvider;

class DotEnvLicenseKeyProvider extends EnvironmentVariableLicenseKeyProvider
{
    /**
     * @var DotEnvAdapterInterface
     */
    private $dotEnvAdapter;

    public function __construct(DotEnvAdapterInterface $dotEnvAdapter)
    {
        $this->dotEnvAdapter = $dotEnvAdapter;
    }

    /**
     * @inheritDoc
     */
    public function provide(): ?string
    {
        $currentWorkingDirectory = getcwd();
        if ($currentWorkingDirectory !== false) {
            $this->dotEnvAdapter->load($currentWorkingDirectory);
        }
        return parent::provide();
    }
}
