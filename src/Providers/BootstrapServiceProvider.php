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
        $this->app[config('modules.registration_name', 'modules')]->boot();
    }

    /**
     * Register the provider.
     */
    public function register()
    {
        $this->app[config('modules.registration_name', 'modules')]->register();
    }
}
