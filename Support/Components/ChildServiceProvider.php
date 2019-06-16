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
        $menu = $this->getMenu();

        if ($adminSidenav = \Menu::instance('admin.sidenav')) {
            if ($menu && count($menu)) {
                foreach ($menu as $menu_item) {
                    $route = (!empty($menu_item['route'])) ? $menu_item['route'] : $this->getName();
                    $icon = (!empty($menu_item['icon'])) ? $menu_item['icon'] : 'fa fa-fw fa-comments-o';
                    $name = (!empty($menu_item['i18n_name'])) ? $menu_item['i18n_name'] : 'admin/sidenav.name';

                    if (isset($menu_item['child']) && count($menu_item['child'])) {
                        $adminSidenav->dropdown(
                            $this->getNavname().$name,
                            function ($sub) use ($menu_item) {
                                foreach ($menu_item['child'] as $menu_child) {
                                    $route = (!empty($menu_child['route'])) ? $menu_child['route'] : $this->getName();
                                    $icon = (!empty($menu_child['icon'])) ? $menu_child['icon'] : 'fa fa-fw fa-comments-o';
                                    $name = (!empty($menu_child['i18n_name'])) ? $menu_child['i18n_name'] : 'admin/sidenav.name';

                                    $sub->route($this->getAdminRoutePrefix().$route, $this->getNavname().$name, null, [
                                        'icon' => $icon,
                                    ]);
                                }
                            },
                            null,
                            ['icon' => $icon]
                        );
                    } elseif (!isset($menu_item['child'])) {
                        $adminSidenav->route($this->getAdminRoutePrefix().$route, $this->getNavname().$name, null, [
                            'icon' => $icon,
                        ]);
                    }
                }
            }
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
