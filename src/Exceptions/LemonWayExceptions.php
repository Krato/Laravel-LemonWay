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
        if ($errorCode == 147) {
            return new static('This wallet ID and user email are not existent');
        }

        if ($errorCode == 152) {
            return new static('This client already exists');
        }

        return new static("{$msg} - Code {$errorCode}");
    }

    /**
     * @param string $msg
     */
    public static function isNotATimeStamp($msg)
    {
        return new static("{$msg} is not valid timestamp");
    }

    /**
     * @param string $msg
     */
    public static function ibanIsNotValid()
    {
        return new static('The given iban is not valid');
    }

    /**
     * @param string $msg
     */
    public static function bicSwiftIsNotValid()
    {
        return new static('The given BIC/SWIFT is not valid');
    }
}
