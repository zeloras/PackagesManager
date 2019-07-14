<?php

if (!function_exists('giveMeTheModuleName')) {
    /**
     * Retur admin prefx with uri string.
     *
     * @param string $class
     * @param null|string $default
     *
     * @return null|string
     */
    function giveMeTheModuleName($class, $default = null)
    {
        preg_match_all('/([^\\\]+\\\){1}(?<module>.*?)\\\/ims', $class, $module_names);

        return (isset($module_names['module'][0])) ? $module_names['module'][0] : $default;
    }
}

if (! function_exists('module_path')) {
    function module_path($name)
    {
        $module = app('modules')->find($name);
        return $module->getPath();
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return app()->make('path.public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}