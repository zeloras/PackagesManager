<?php

namespace GeekCms\PackagesManager\Facades;

use GeekCms\PackagesManager\Support\Components\CoreComponent;
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
        $returned = null;
        $module_name = giveMeTheModuleName(static::class, null);
        $settings = \Config::get(\Gcms::MODULES_PREFIX.strtolower($module_name), null);

        if (empty($settings)) {
            $module_config = module_path($module_name).\DIRECTORY_SEPARATOR.CoreComponent::CONFIG_PATH;
            if (file_exists($module_config) && is_file($module_config)) {
                $settings = require $module_config;
            }
        }

        if (!empty($settings)) {
            if (isset($settings['FacadeName']) && !\is_array($settings['FacadeName'])) {
                $returned = $settings['FacadeName'];
            }

            if (isset($settings['FacadeName']['alias']) && \is_array($settings['FacadeName'])) {
                $returned = $settings['FacadeName']['alias'];
            }
        }

        return $returned;
    }
}
