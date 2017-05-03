<?php

namespace Infinety\LemonWay;

use Illuminate\Support\ServiceProvider;
use Infinety\LemonWay\LemonWay;

/**
 *
 * Laravel wrapper for Directus API
 *
 * @category   Laravel Directus
 * @version    1.0.0
 * @package    theplanworks/directus-laravel
 * @copyright  Copyright (c) 2017 thePLAN (http://www.theplanworks.com)
 * @author     Matt Fox <matt.fox@theplanworks.com>
 * @license    https://opensource.org/licenses/MIT    MIT
 */
class LemonWayServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/lemonway.php' => config_path('lemonway.php'),
        ]);
    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        //
        $this->mergeConfigFrom(
            __DIR__ . '/config/lemonway.php',
            'lemonway'
        );

        $this->app->bind('search', 'Acme\Search\Search');

        $this->app->bind('LemonWay', function ($app) {
            return new LemonWay();
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {

        return [LemonWay::class];
    }
}
