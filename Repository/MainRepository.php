<?php

namespace GeekCms\PackagesManager\Repository;

use GeekCms\PackagesManager\Repository\Template\MainRepositoryAbstract;
use Nwidart\Modules\Laravel\Module;

class MainRepository extends MainRepositoryAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        return new Module(...$args);
    }
}