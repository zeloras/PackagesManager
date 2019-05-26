<?php

namespace GeekCms\PackagesManager\Repository;

use Nwidart\Modules\Laravel\Module;
use Nwidart\Modules\Module as MainModule;
use Nwidart\Modules\FileRepository;


class MainRepository extends FileRepository
{
    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        return new Module(...$args);
    }

    /**
     * Sort all downloaded modules by priority for forward init.
     *
     * @param array $modules
     * @param array $sorted_list
     *
     * @return array
     */
    public function sortModulesListPriority(array $modules = [], $sorted_list = []) : array
    {
        /**
         * Function for cut namespace for work with results forward
         *
         * @param string $namespace
         * @param int $size
         * @return string|string[]|null
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

        /**
         * Sort array by load "weight"
         */
        uasort($modules, function (MainModule $a, MainModule $b) use ($sorted_list, $trims) {
            $amin = $bmin = 0;
            if (!empty($sorted_list)) {
                $left_path = $trims($a->get('providers', [null])[0], 2);
                $right_path = $trims($b->get('providers', [null])[0], 2);
                $amin = (isset($sorted_list[$left_path])) ? $sorted_list[$left_path] : 0;
                $bmin = (isset($sorted_list[$right_path])) ? $sorted_list[$right_path] : 0;
            }

            $left = $a->order + $amin;
            $right = $b->order + $bmin;

            if ($left == $right) {
                return (int) $left;
            }

            return $left > $right ? 1 : -1;
        });

        return $modules;
    }

    /**
     * Function for call some sort functions,
     * for recursive sort and build list
     * for register active modules
     *
     * @return array
     */
    public function listByPriority()
    {
        $local_modules = $this->allEnabled();

        $sorted = $this->sortModulesListPriority($local_modules);
        $preload_modules = [];
        $count = 0;

        foreach ($sorted as $key => $module) {
            $requirements = $module->getRequires();
            foreach ($requirements as $item) {
                if (isset($preload_modules[$item])) {
                    continue;
                }

                $check = preg_grep("/^" . preg_quote($item, "/") . ".*?/i", get_declared_classes());
                if ($check) {
                    $preload_modules[$item] = $count;
                    $count++;
                }
            }
        }

        $sorted = $this->sortModulesListPriority($local_modules, $preload_modules);

        return $sorted;
    }

    /**
     * Switch to local modules class
     *
     * @return LocalRepository
     */
    public function setLocal()
    {
        return new LocalRepository($this->app, $this->path, $this);
    }

    /**
     * Switch to remote modules class
     *
     * @return RemoteRepository
     */
    public function setRemote()
    {
        return new RemoteRepository($this->app, $this->path, $this);
    }

    /**
     * @inheritDoc
     */
    public function setMain()
    {
        return $this;
    }
}