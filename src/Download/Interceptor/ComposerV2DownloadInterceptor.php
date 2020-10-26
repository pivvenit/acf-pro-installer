<?php


namespace PivvenIT\Composer\Installers\ACFPro\Download\Interceptor;

use Composer\Plugin\PreFileDownloadEvent;

class ComposerV2DownloadInterceptor implements DownloadInterceptorInterface
{
    /**
     * @inheritDoc
     */
    public function intercept(PreFileDownloadEvent $event, string $modifiedDownloadUrl): void
    {
        $event->setProcessedUrl($modifiedDownloadUrl);
    }
}
