<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv;

use Dotenv\Dotenv;

class DotEnvV4Adapter implements DotEnvAdapterInterface
{
    /**
     * @inheritDoc
     */
    public function load(string $path): void
    {
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->safeLoad();
    }
}
