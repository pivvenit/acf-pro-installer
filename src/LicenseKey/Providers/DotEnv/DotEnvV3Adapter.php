<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DotEnv;

use Dotenv\Dotenv;

/**
 * @codeCoverageIgnore
 */
class DotEnvV3Adapter implements DotEnvAdapterInterface
{
    /**
     * @inheritDoc
     */
    public function load(string $path): void
    {
        /**
         * @noinspection PhpParamsInspection This is fallback code
         */
        $dotenv = Dotenv::create(getcwd());
        $dotenv->safeLoad();
    }
}
