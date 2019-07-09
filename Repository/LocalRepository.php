<?php

namespace GeekCms\PackagesManager\Repository;

use GeekCms\PackagesManager\Repository\Template\MainRepositoryAbstract;

class LocalRepository extends MainRepositoryAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getOfficialPackages()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getUnofficialPackages()
    {
        return [];
    }
}
