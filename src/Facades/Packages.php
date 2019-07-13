<?php

namespace GeekCms\PackagesManager\Facades;

use Config;
use Gcms;
use GeekCms\PackagesManager\Support\MainServiceProvider;
use Illuminate\Support\Facades\Facade;
use function is_array;
use const DIRECTORY_SEPARATOR;

class Packages extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        $returned = null;
        $module_name = giveMeTheModuleName(static::class, null);
        $settings = Config::get(Gcms::MODULES_PREFIX . strtolower($module_name), null);

        if (empty($settings)) {
            $module_config = module_path($module_name) . DIRECTORY_SEPARATOR . MainServiceProvider::CONFIG_PATH;
            if (file_exists($module_config) && is_file($module_config)) {
                $settings = require $module_config;
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

        return $returned;
    }
}
