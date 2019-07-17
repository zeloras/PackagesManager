<?php

if (!function_exists('module_package_name')) {
    /**
     * Retur admin prefx with uri string.
     *
     * @param string $class
     * @param null|string $default
     *
     * @return null|string
     */
    function module_package_name($class, $default = null)
    {
        preg_match_all('/([^\\\]+\\\){1}(?<module>.*?)\\\/ims', $class, $module_names);

        return $module_names['module'][0] ?? $default;
    }
}

if (! function_exists('module_path')) {
    function module_path($name)
    {
        $app = app(config('modules.registration_name', 'modules'));
        $module = ($app) ?  $app->find($name)->getPath() : false;
        return $module;
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
        return app()->basePath() . '/config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
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

if (! function_exists('namespace_use_file')) {
    /**
     * For get full namespace by real filepath
     *
     * @param null $file
     * @return string|null
     */
    function namespace_use_file($file = null)
    {
        if (file_exists($file) && is_file($file)) {
            $get_conent = file_get_contents($file);
            if ($get_conent) {
                preg_match_all('#^namespace\s+(?<space>.+?);$|^class\s+(?<name>[^\s\W]+)\W*#smui', $get_conent, $results);
                $get_conent = null;
                unset($get_conent);
                if (isset($results['space'][0], $results['name'][1])) {
                    return $results['space'][0] . '\\' . $results['name'][1];
                }
            }
        }

        return null;
    }
}