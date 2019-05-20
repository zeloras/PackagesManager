<?php
namespace GeekCms\PackagesManager\Facades;

use Illuminate\Support\Facades\Facade;


class PackageManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'packageManager';
    }
}