<?php

namespace GeekCms\PackagesManager\Facades;

use Config;
use Illuminate\Support\Facades\Facade;
use function is_array;
use const DIRECTORY_SEPARATOR;

class PackageSystem extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        $returned = null;
        $module_name = module_package_name(static::class);
        $settings = Config::get(config('modules.module_prefix') . strtolower($module_name));

        if (empty($settings)) {
            $main_config = module_path($module_name) . DIRECTORY_SEPARATOR . config('modules.paths.main_config_path');
            if (file_exists($main_config) && is_file($main_config)) {
                $settings = require $main_config;
            }
        }

        if (!empty($settings)) {
            if (isset($settings['FacadeName']) && !is_array($settings['FacadeName'])) {
                $returned = $settings['FacadeName'];
            }

            if (is_array($settings) && isset($settings['FacadeName']['alias'])) {
                $returned = $settings['FacadeName']['alias'];
            }
        }

        if (class_exists($returned)) {
            //$returned = EmptyFake::class;
            //throw new \Exception('Facade duplicate');
        }

        return $returned;
    }
}
