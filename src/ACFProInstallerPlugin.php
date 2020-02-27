<?php

namespace PivvenIT\Composer\Installers\ACFPro;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use PivvenIT\Composer\Installers\ACFPro\Download\DownloadMatcher;
use PivvenIT\Composer\Installers\ACFPro\Download\DownloadMatcherInterface;
use PivvenIT\Composer\Installers\ACFPro\Download\RewriteUrlRemoteFilesystem;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Appenders\UrlLicenseKeyAppender;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Appenders\UrlLicenseKeyAppenderInterface;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DefaultLicenseKeyProviderFactory;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\LicenseKeyProviderFactoryInterface;

/**
 * A composer plugin that makes installing ACF PRO possible
 *
 * The WordPress plugin Advanced Custom Fields PRO (ACF PRO) does not
 * offer a way to install it via composer natively.
 *
 * This plugin checks for ACF PRO downloads, and then appends the provided license key to the download URL
 *
 * With this plugin user no longer need to expose their license key in
 * composer.json.
 */
class ACFProInstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @access protected
     * @var    Composer
     */
    protected $composer;

    /**
     * @access protected
     * @var    IOInterface
     */
    protected $io;

    /**
     * @var LicenseKeyProviderFactoryInterface
     */
    private $licenseKeyProviderFactory;
    /**
     * @var UrlLicenseKeyAppenderInterface
     */
    private $urlLicenseKeyAppender;
    /**
     * @var DownloadMatcherInterface
     */
    private $downloadMatcher;

    /**
     * ACFProInstallerPlugin constructor.
     *
     * @param LicenseKeyProviderFactoryInterface|null $licenseKeyProviderFactory
     * @param UrlLicenseKeyAppenderInterface|null     $urlLicenseKeyAppender
     * @param DownloadMatcherInterface|null           $downloadMatcher
     */
    public function __construct(
        LicenseKeyProviderFactoryInterface $licenseKeyProviderFactory = null,
        UrlLicenseKeyAppenderInterface $urlLicenseKeyAppender = null,
        DownloadMatcherInterface $downloadMatcher = null
    ) {
        $this->licenseKeyProviderFactory = $licenseKeyProviderFactory ?? new DefaultLicenseKeyProviderFactory();
        $this->urlLicenseKeyAppender = $urlLicenseKeyAppender ?? new UrlLicenseKeyAppender();
        $this->downloadMatcher = $downloadMatcher ?? new DownloadMatcher();
    }

    /**
     * The function that is called when the plugin is activated
     *
     * Makes composer and io available because they are needed
     * in the addKey method.
     *
     * @access public
     * @param  Composer    $composer The composer object
     * @param  IOInterface $io       Not used
     */
    public function activate(Composer $composer, IOInterface $io) : void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Subscribe this Plugin to relevant Events
     *
     * Pre Download: The key needs to be added to the url
     *               (will not show up in composer.lock)
     *
     * @access public
     * @return array<string, string> An array of events that the plugin subscribes to
     * @static
     */
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PRE_FILE_DOWNLOAD => 'onPreFileDownload'
        ];
    }

    /**
     * Checks if the download is an ACF package, if so it appends the license key to the URL
     *
     * The key is not added to the package because it would show up in the
     * composer.lock file in this case. A custom file system is used to
     * swap out the ACF PRO url with a url that contains the key.
     *
     * @access public
     * @param  PreFileDownloadEvent $event The event that called this method
     * @throws MissingKeyException
     */
    public function onPreFileDownload(PreFileDownloadEvent $event) : void
    {
        $packageUrl = $event->getProcessedUrl();

        if (!$this->downloadMatcher->matches($packageUrl)) {
            return;
        }
        $remoteFilesystem = $event->getRemoteFilesystem();
        $event->setRemoteFilesystem(
            new RewriteUrlRemoteFilesystem(
                $this->getDownloadUrl($packageUrl),
                $this->io,
                $this->composer->getConfig(),
                $remoteFilesystem->getOptions(),
                $remoteFilesystem->isTlsDisabled()
            )
        );
    }

    /**
     * @param  string $packageUrl
     * @return string
     * @throws MissingKeyException
     */
    private function getDownloadUrl(string $packageUrl): string
    {
        return $this->urlLicenseKeyAppender->append($packageUrl, $this->getLicenseKey());
    }


    /**
     * Get the ACF PRO license key
     *
     * @access protected
     * @return string The key from the environment
     * @throws MissingKeyException
     */
    private function getLicenseKey()
    {
        $licenseKeyProvider = $this->licenseKeyProviderFactory->build($this->composer, $this->io);
        $key = $licenseKeyProvider->provide();
        if ($key === null) {
            throw new MissingKeyException("No valid license key could be found");
        }
        return $key;
    }
}
