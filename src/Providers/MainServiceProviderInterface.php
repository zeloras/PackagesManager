<?php

namespace GeekCms\PackagesManager\Providers;

interface MainServiceProviderInterface
{
    /**
     * For custom initialization modules service provider
     *
     * @param array ...$args
     * @return null|\stdClass
     */
    public static function mainInit(array ...$args);
}
