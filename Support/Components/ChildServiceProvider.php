<?php

namespace GeekCms\PackagesManager\Support\Components;

use Illuminate\Foundation\AliasLoader;

/**
 * Class ChildServiceProvider.
 */
class ChildServiceProvider extends CoreComponent
{
    /**
     * {@inheritdoc}
     */
    public function registerNavigation()
    {
        if ($adminSidenav = \Menu::instance('admin.sidenav')) {
            $adminSidenav->route($this->getAdminRoutePrefix().$this->getName(), $this->getNavname(), null, [
                'icon' => 'fa fa-fw fa-comments-o',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerFacades()
    {
        /**
         * Try load and set alias for "light" version, light version it's like a helper.
         */
        $config = $this->getModuleConfig();
        $loader = AliasLoader::getInstance();
        if (isset($config['FacadeName']['alias']) && \is_array($config['FacadeName'])) {
            try {
                $path = base_path(ucfirst(self::PATH_MODULES));
                $aliasName = $config['FacadeName']['alias'];
                $facadeClass = \get_class(new $config['FacadeName']['facadePath']());
                $repoClass = $config['FacadeName']['mainRepoPath'];

                if (!class_exists($aliasName)) {
                    if (method_exists($repoClass, 'getInstance')) {
                        $this->app->bind($aliasName, function ($app) use ($repoClass) {
                            return (new $repoClass())::getInstance();
                        });
                        $this->app->instance(\get_class(new $repoClass()), $repoClass::getInstance());
                        $loader->alias($facadeClass, $aliasName);
                        class_alias($facadeClass, $aliasName);
                    } else {
                        $this->app->bind($aliasName, function ($app) use ($repoClass, $path) {
                            return new $repoClass($app, $path);
                        });

                        $this->app->singleton($aliasName, function ($app) use ($repoClass, $path) {
                            return new $repoClass($app, $path);
                        });
                        $loader->alias($facadeClass, $aliasName);
                        class_alias($facadeClass, $aliasName);
                    }
                }
            } catch (\Exception $e) {
                $this->getModuleLogs()->error($e);

                throw new \Exception($e);
            }
        }
    }
}
