<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers;

class CompositeLicenseKeyProvider implements LicenseKeyProviderInterface
{
    /**
     * @var LicenseKeyProviderInterface[]
     */
    private $providers;

    /**
     * CompositeLicenseKeyProvider constructor.
     *
     * @param LicenseKeyProviderInterface ...$providers Providers to wrap
     */
    public function __construct(LicenseKeyProviderInterface ...$providers)
    {
        $this->providers = $providers;
    }

    /**
     * @inheritDoc
     */
    public function provide(): ?string
    {
        foreach ($this->providers as $provider) {
            $result = $provider->provide();
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    /**
     * @return LicenseKeyProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
