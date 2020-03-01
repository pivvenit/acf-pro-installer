<?php
declare(strict_types=1);

namespace PivvenIT\Composer\Installers\ACFPro\Exceptions;

use \Exception;

/**
 * Exception thrown if the ACF PRO key is not available in the environment
 */
class MissingKeyException extends Exception
{
    /**
     * MissingKeyException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(
        $message = '',
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct(
            "Could not find a license key for ACF PRO. {$message}",
            $code,
            $previous
        );
    }
}
