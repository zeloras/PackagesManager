<?php

namespace GeekCms\PackagesManager\Repository;

use Illuminate\Container\Container;
use GeekCms\PackagesManager\Exceptions\ModuleNotFoundException;
use GeekCms\PackagesManager\Modules\Module;

abstract class MainRepositoryAbstract extends FileRepository
{
    /**
     * Constants for filter something or get lists with needed packages.
     */
    const PACKAGE_OFFICIAL = 'official';
    const PACKAGE_UNOFFICIAL = 'unofficial';
    const PACKAGE_INSTALLED_ALL = 'all';
    const PACKAGE_INSTALLED_ACTIVE = 'installed-active';
    const PACKAGE_INSTALLED_DISABLED = 'installed-disabled';
    const PACKAGE_REMOTE_OFFICIAL = 'remote-official';
    const PACKAGE_REMOTE_UNOFFICIAL = 'remote-unofficial';

    const REPO_USER_LINK = 'https://api.github.com/users/*name*/repos';
    const REPO_GROUP_LINK = 'https://api.github.com/orgs/*name*/repos';

    /**
     * For github api
     */
    const REPO_MODULE_LINK_RELEASES = '/releases';
    const REPO_MODULE_LINK_MODULE_CONTENT = '/contents/module.json';
    const REPO_MODULE_LINK_COMPOSER_CONTENT = '/contents/composer.json';

    /**
     * Key for cache curl responses
     *
     * @var string
     */
    const CACHED_MODULES_LIST_KEY = 'get_url_cached_modules';

    /**
     * Load classes for work with remote packages or local(downloaded).
     *
     * @var string
     */
    protected $packages_local = LocalPackage::class;
    protected $packages_remote = RemoteRepository::class;

    /**
     * Repositories with official and unofficial modules.
     *
     * @var array
     */
    protected $modules = [self::PACKAGE_OFFICIAL => [], self::PACKAGE_UNOFFICIAL => []];

    /**
     * For switch to back main class.
     *
     * @var
     */
    protected $main_instance;

    /**
     * MainRepositoryAbstract constructor.
     *
     * @param Container $app
     * @param null $path
     * @param MainRepository $instance
     */
    public function __construct(Container $app, $path = null, MainRepository $instance = null)
    {
        $this->main_instance = $instance;
        parent::__construct($app, $path);
    }

    /**
     * Get official packages list.
     *
     * @return LocalPackage
     * @throws ModuleNotFoundException
     *
     */
    public function getOfficialPackages()
    {
        return $this->modules[self::PACKAGE_OFFICIAL];
    }


    /**
     * Get unofficial packages list.
     *
     * @return LocalPackage
     * @throws ModuleNotFoundException
     *
     */
    public function getUnofficialPackages()
    {
        return $this->modules[self::PACKAGE_UNOFFICIAL];
    }

    /**
     * Switch to main class.
     *
     * @return MainRepository
     */
    public function setMain()
    {
        return $this->main_instance;
    }

    /**
     * Switch to main class.
     *
     * @return MainRepository|string
     */
    public function getHandler()
    {
        return $this->packages_local;
    }

    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        return new Module(...$args);
    }
}
