<?php

namespace GeekCms\PackagesManager\Repository;

use Nwidart\Modules\FileRepository as ModuleRepositrory;
use Nwidart\Modules\Laravel\Module;

class FileRepository extends ModuleRepositrory
{
    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        return new Module(...$args);
    }
}