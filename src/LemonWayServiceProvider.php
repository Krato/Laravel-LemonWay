<?php

namespace Infinety\LemonWay;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel wrapper for Lemonaway
 *
 * @category   Laravel Directus
 *
 * @version    1.0.0
 *
 * @author     Eric Lagarda <eric@infinety.com>
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
            __DIR__.'/config/lemonway.php' => config_path('lemonway.php'),
        ]);

        $this->addValidations();
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
            __DIR__.'/config/lemonway.php',
            'lemonway'
        );

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

    private function addValidations()
    {
        Validator::extend('iban', 'Infinety\LemonWay\Validations\IbanValidator@validate');
        Validator::extend('bic_swift', 'Infinety\LemonWay\Validations\BicSwiftValidator@validate');
    }
}
