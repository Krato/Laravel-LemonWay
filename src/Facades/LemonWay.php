<?php

namespace Infinety\LemonWay\Facades;

use Illuminate\Support\Facades\Facade;

class LemonWay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'LemonWay';
    }
}
