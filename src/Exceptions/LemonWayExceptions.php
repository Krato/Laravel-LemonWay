<?php

namespace Infinety\LemonWay\Exceptions;

use Exception;

class LemonWayExceptions extends Exception
{
    /**
     * @param string $msg
     * @param string $errorCode
     */
    public static function apiError(string $msg, string $errorCode)
    {
        return new static("{$msg} - Code error {$errorCode}");
    }
}
