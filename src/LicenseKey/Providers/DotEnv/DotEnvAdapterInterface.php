<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv;

interface DotEnvAdapterInterface
{
    /**
     * Expects DotEnv file to be loaded and made variables to be made available to getenv if available.
     * It should fail silently if it does not exist.
     *
     * @param string $path Path to the directory in which to look for .env files
     *
     * @return void
     */
    public function load(string $path): void;
}
