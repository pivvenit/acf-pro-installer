<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Download\Interceptor;

use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;

/**
 * A composer remote filesystem for ACF PRO
 *
 * Makes it possible to copy files from a modified file url
 *
 * @deprecated This class is used to provide backwards compatible support for V1 Composer environments
 */
class RewriteUrlRemoteFilesystem extends RemoteFilesystem
{
    /**
     * The rewriteUrl that is used instead of the provided Url
     *
     * @access protected
     * @var    string
     */
    protected $rewriteUrl;

    /**
     * Constructor
     *
     * @access public
     * @param string $rewriteUrl The url that should be used instead of fileurl
     * @param IOInterface $io The IO instance
     * @param Config $config The config
     * @param array $options The options
     * @param bool $disableTls
     */
    public function __construct(
        $rewriteUrl,
        IOInterface $io,
        Config $config,
        array $options = [],
        $disableTls = false
    ) {
        $this->rewriteUrl = $rewriteUrl;
        parent::__construct($io, $config, $options, $disableTls);
    }

    /**
     * Copy the remote file in local
     *
     * Uses the provided rewriteUrl instead of the provided $fileUrl
     *
     * @param string $originUrl The origin URL
     * @param string $fileUrl   The file URL (ignored)
     * @param string $fileName  the local filename
     * @param bool   $progress  Display the progression
     * @param array  $options   Additional context options
     *
     * @return bool true
     */
    public function copy(
        $originUrl,
        $fileUrl,
        $fileName,
        $progress = true,
        $options = []
    ) {
        return parent::copy(
            $originUrl,
            $this->rewriteUrl,
            $fileName,
            $progress,
            $options
        );
    }
}
