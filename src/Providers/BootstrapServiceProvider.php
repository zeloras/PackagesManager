<?php

namespace GeekCms\PackagesManager\Providers;

use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        $app = $this->app[config('modules.registration_name', 'modules')];
        if ($app) {
            $app->boot();
        }
    }

    /**
     * Register the provider.
     */
    public function register()
    {
        $app = $this->app[config('modules.registration_name', 'modules')];
        if ($app) {
            $app->register();
        }
    }
}
