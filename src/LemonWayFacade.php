<?php

namespace Infinety\LemonWay;

use Illuminate\Support\Facades\Facade;

class LemonWayFacade extends Facade
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
