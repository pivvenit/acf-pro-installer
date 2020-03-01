<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers;

/**
 * Interface for different kinds of loading an ACF PRO License Key
 *
 * Interface LicenseKeyProviderInterface
 *
 * @package PivvenIT\Composer\Installers\ACFPro\LicenseKeyProviders
 */
interface LicenseKeyProviderInterface
{
    /**
     * Returns the license key
     *
     * @return string|null
     */
    public function provide(): ?string;
}
