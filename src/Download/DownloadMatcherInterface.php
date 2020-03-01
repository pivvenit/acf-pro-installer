<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Download;

interface DownloadMatcherInterface
{
    /**
     * Returns if this download matches an ACF Package URL
     *
     * @param  string $url
     * @return bool
     */
    public function matches(string $url): bool;
}
