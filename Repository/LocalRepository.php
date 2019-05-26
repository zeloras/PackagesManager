<?php

namespace GeekCms\PackagesManager\Repository;

use GeekCms\PackagesManager\Repository\Template\MainRepositoryAbstract;

class LocalRepository extends MainRepositoryAbstract
{
    /**
     * @inheritDoc
     */
    public function getOfficialPackages()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getUnofficialPackages()
    {
        return [];
    }
}