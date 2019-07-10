<?php

namespace GeekCms\PackagesManager\Repository;

use Gcms;
use GeekCms\PackagesManager\Modules\Module;
use GeekCms\PackagesManager\Repository\Components\ManageLocalPackage;
use GeekCms\PackagesManager\Support\Components\ChildServiceProvider;
use Illuminate\Container\Container;
use Nwidart\Modules\Exceptions\ModuleNotFoundException;
use Nwidart\Modules\FileRepository;

class MainRepository extends FileRepository
{
    /**
     * Var with RemoteRepository instance
     *
     * @var null
     */
    protected $main_repo_app = null;

    /**
     * The constructor.
     *
     * @param Container $app
     * @param null|string $path
     */
    public function __construct(Container $app, $path = null)
    {
        // @todo fixit
        $this->app = $app;
        $this->path = (!empty($path)) ? $path : base_path(ucfirst(ChildServiceProvider::PATH_MODULES));
        $this->main_repo_app = new RemoteRepository($this->app, $this->path, $this);
        parent::__construct($app, $path);
    }

    /**
     * Function for call some sort functions,
     * for recursive sort and build list
     * for register active modules.
     *
     * @return array
     */
    public function listByPriority()
    {
        $local_modules = \Module::allEnabled();

        $sorted = $this->sortModulesListPriority($local_modules);
        $preload_modules = [];
        $count = 0;

        foreach ($sorted as $key => $module) {
            $requirements = $module->getRequires();

            foreach ($requirements as $item) {
                if (isset($preload_modules[$item])) {
                    continue;
                }

                $check = preg_grep('/^' . preg_quote($item, DIRECTORY_SEPARATOR) . '.*?/i', get_declared_classes());
                if ($check) {
                    $preload_modules[$item] = $count;
                    ++$count;
                }
            }
        }

        return $this->sortModulesListPriority($local_modules, $preload_modules);
    }

    /**
     * Sort all downloaded modules by priority for forward init.
     *
     * @param array $modules
     * @param array $sorted_list
     *
     * @return array
     */
    public function sortModulesListPriority(array $modules = [], $sorted_list = []): array
    {
        /**
         * Function for cut namespace for work with results forward.
         *
         * @param string $namespace
         * @param int $size
         *
         * @return null|string|string[]
         */
        $trims = function ($namespace = '', int $size = 1) {
            if (!empty($namespace)) {
                preg_match_all('/^(?<module>([^\\\]+\\\){' . $size . '})/imus', $namespace, $find);
                if (isset($find['module'][0]) && !empty($find['module'][0])) {
                    $namespace = preg_replace('/\\\$/ims', '', $find['module'][0]);
                }
            }

            return $namespace;
        };

        // Sort array by load "weight"
        uasort($modules, function ($a, $b) use ($sorted_list, $trims) {
            $amin = $bmin = 0;
            if (!empty($sorted_list)) {
                $left_path = $trims($a->get('providers', [null])[0], 2);
                $right_path = $trims($b->get('providers', [null])[0], 2);
                $amin = (isset($sorted_list[$left_path])) ? $sorted_list[$left_path] : 0;
                $bmin = (isset($sorted_list[$right_path])) ? $sorted_list[$right_path] : 0;
            }

            $left = $a->order + $amin;
            $right = $b->order + $bmin;

            if ($left === $right) {
                return (int)$left;
            }

            return $left > $right ? 1 : -1;
        });

        return $modules;
    }

    /**
     * Get modules instance.
     *
     * @return RemoteRepository
     */
    public function getModules()
    {
        return $this->main_repo_app;
    }

    public function findAndInstall($module)
    {
        $installed = false;
        $handler = $this->main_repo_app->getHandler();
        $modules = new $handler($this->main_repo_app->getOfficialPackages());
        $for_install = array_filter($modules->forInstall(), function ($v) use ($module) {
            return ($v['module_info']['name'] == $module);
        });

        if (count($for_install)) {
            $module = array_values($for_install);
            $module = $module[0];

            if (!empty($module)) {
                $package = new ManageLocalPackage();
                $installer = $package->install($module, $this->path);
                dd($installer);
            }
        }

        return $installed;
    }

    /**
     * Get official modules
     *
     * @return LocalPackage|mixed
     * @throws ModuleNotFoundException
     */
    public function getModulesOfficial()
    {
        $handler = $this->main_repo_app->getHandler();
        return new $handler($this->main_repo_app->getOfficialPackages());
    }

    /**
     * Get unofficial modules
     *
     * @return LocalPackage|mixed
     * @throws ModuleNotFoundException
     */
    public function getModulesUnOfficial()
    {
        $handler = $this->main_repo_app->getHandler();
        return new $handler($this->main_repo_app->getUnofficialPackages());
    }

    /**
     * {@inheritdoc}
     */
    public function setMain()
    {
        return $this;
    }

    /**
     * Get list with permissions for every enabled module.
     *
     * @return array
     */
    public function getPermissionsList()
    {
        $local_modules = \Module::allEnabled();
        $permissions = [];
        foreach ($local_modules as $module) {
            $permissions[$module->name] = $module->get(Gcms::CONFIG_ADMIN_PERMISSION, []);
        }

        return $permissions;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function createModule(...$args)
    {
        return new Module(...$args);
    }
}
