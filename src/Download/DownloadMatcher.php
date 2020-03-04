<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Download;

class DownloadMatcher implements DownloadMatcherInterface
{
    /**
     * The url where ACF PRO can be downloaded (without version and key)
     */
    private const ACF_PRO_PACKAGE_URL = 'https://connect.advancedcustomfields.com/v2/plugins/download?p=pro';


    private const ACF_PRO_LEGACY_PACKAGE_URL = 'https://connect.advancedcustomfields.com/index.php?p=pro&a=download';

    /**
     * Returns if this download matches an ACF Package URL
     *
     * @param  string $url
     * @return bool
     */
    public function matches(string $url): bool
    {
        return strpos($url, self::ACF_PRO_LEGACY_PACKAGE_URL) !== false ||
            strpos($url, self::ACF_PRO_PACKAGE_URL) !== false;
    }
}
