<?php

namespace PivvenIT\Composer\Installers\ACFPro;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use PivvenIT\Composer\Installers\ACFPro\Exceptions\MissingKeyException;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\DefaultLicenseKeyProviderFactory;
use PivvenIT\Composer\Installers\ACFPro\LicenseKey\Providers\LicenseKeyProviderFactoryInterface;

/**
 * A composer plugin that makes installing ACF PRO possible
 *
 * The WordPress plugin Advanced Custom Fields PRO (ACF PRO) does not
 * offer a way to install it via composer natively.
 *
 * This plugin uses a 'package' repository (user supplied) that downloads the
 * correct version from the ACF site using the version number from
 * that repository and a license key from the ENVIRONMENT or an .env file.
 *
 * With this plugin user no longer need to expose their license key in
 * composer.json.
 */
class ACFProInstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * The name of the ACF PRO package
     */
    const ACF_PRO_PACKAGE_NAME = 'advanced-custom-fields/advanced-custom-fields-pro';

    /**
     * The url where ACF PRO can be downloaded (without version and key)
     */
    const ACF_PRO_PACKAGE_URL = 'https://connect.advancedcustomfields.com/index.php?p=pro&a=download';

    /**
     * @access protected
     * @var Composer
     */
    protected $composer;

    /**
     * @access protected
     * @var IOInterface
     */
    protected $io;

    /**
     * @var LicenseKeyProviderFactoryInterface
     */
    private $licenseKeyProviderFactory;

    /**
     * ACFProInstallerPlugin constructor.
     *
     * @param LicenseKeyProviderFactoryInterface|null $licenseKeyProviderFactory
     */
    public function __construct(LicenseKeyProviderFactoryInterface $licenseKeyProviderFactory = null)
    {
        $this->licenseKeyProviderFactory = $licenseKeyProviderFactory ?? new DefaultLicenseKeyProviderFactory();
    }

    /**
     * The function that is called when the plugin is activated
     *
     * Makes composer and io available because they are needed
     * in the addKey method.
     *
     * @access public
     * @param Composer $composer The composer object
     * @param IOInterface $io Not used
     */
    public function activate(Composer $composer, IOInterface $io)
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
     * @return array An array of events that the plugin subscribes to
     * @static
     */
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PRE_FILE_DOWNLOAD => 'addKey'
        ];
    }

    /**
     * Add the key from the environment to the event url
     *
     * The key is not added to the package because it would show up in the
     * composer.lock file in this case. A custom file system is used to
     * swap out the ACF PRO url with a url that contains the key.
     *
     * @access public
     * @param PreFileDownloadEvent $event The event that called this method
     * @throws MissingKeyException
     */
    public function addKey(PreFileDownloadEvent $event)
    {
        $packageUrl = $event->getProcessedUrl();

        if ($this->isAcfProPackageUrl($packageUrl)) {
            $rfs = $event->getRemoteFilesystem();
            $acfRfs = new RewriteUrlRemoteFilesystem(
                $this->appendLicenseKey($packageUrl),
                $this->io,
                $this->composer->getConfig(),
                $rfs->getOptions(),
                $rfs->isTlsDisabled()
            );
            $event->setRemoteFilesystem($acfRfs);
        }
    }

    /**
     * Test if the given url is the ACF PRO download url
     *
     * @access protected
     * @param string The url that should be checked
     * @return bool
     */
    protected function isAcfProPackageUrl($url)
    {
        return strpos($url, self::ACF_PRO_PACKAGE_URL) !== false;
    }

    /**
     * Get the ACF PRO key from the environment
     *
     * Loads the .env file that is in the same directory as composer.json
     * and gets the key from the environment variable KEY_ENV_VARIABLE.
     * Already set variables will not be overwritten by the variables in .env
     * @link https://github.com/vlucas/phpdotenv#immutability
     *
     * @access protected
     * @return string The key from the environment
     * @throws MissingKeyException
     */
    protected function getKeyFromEnv()
    {
        $licenseKeyProvider = $this->licenseKeyProviderFactory->build($this->composer, $this->io);
        $key = $licenseKeyProvider->provide();
        if ($key === null) {
            throw new MissingKeyException("No valid license key could be found");
        }
        return $key;
    }

    /**
     * Adds the license key to the Advanced Custom Fields Url
     *
     * @param string $url the url to append the key to
     * @return string The new url with the appended license key
     * @throws MissingKeyException
     */
    private function appendLicenseKey($url): string
    {
        $c = parse_url($url);
        $queryParams = [];
        parse_str($c['query'], $queryParams);
        $queryParams['k'] = $this->getKeyFromEnv();
        $c['query'] = http_build_query($queryParams);

        return "{$c['scheme']}://{$c['host']}{$c['path']}?{$c['query']}";
    }
}
