<?php

namespace GeekCms\PackagesManager\Exceptions;

class InvalidAssetPath extends \Exception
{
    public static function missingPackageName($asset)
    {
        return new static("Package name was not specified in asset [$asset].");
    }
}
