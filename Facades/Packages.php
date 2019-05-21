<?php
namespace GeekCms\PackagesManager\Facades;

use Illuminate\Support\Facades\Facade;

class Packages extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        $module_name = \giveMeTheModuleName(static::class, null);
        $settings = \Config::get('module_'.strtolower($module_name), ['FacadeName' => null]);

        return $settings['FacadeName'];
    }
}