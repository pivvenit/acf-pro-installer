<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv;

use Dotenv\Dotenv;

class DotEnvV5Adapter implements DotEnvAdapterInterface
{
    /**
     * @inheritDoc
     */
    public function load(string $path): void
    {
        $dotenv = Dotenv::createUnsafeImmutable($path);
        $dotenv->safeLoad();
    }
}
