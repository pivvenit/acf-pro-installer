<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\LicenseKey\Appenders;

class UrlLicenseKeyAppender implements UrlLicenseKeyAppenderInterface
{

    /**
     * @inheritdoc
     */
    public function append(string $url, string $licenseKey): string
    {
        ['scheme' => $scheme, 'host' => $host, 'path' => $path, 'query' => $query ] = parse_url($url);
        $queryParams = [];
        parse_str($query, $queryParams);
        $queryParams['k'] = $licenseKey;
        $query = http_build_query($queryParams);

        return "{$scheme}://{$host}{$path}?{$query}";
    }
}
