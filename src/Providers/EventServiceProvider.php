<?php

namespace GeekCms\PackagesManager\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider implements MainServiceProviderInterface
{
    protected $listen = [];

    /**
     * @inheritDoc
     */
    public static function mainInit(array ...$args)
    {
        return null;
    }
}
