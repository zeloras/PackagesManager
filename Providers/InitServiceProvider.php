<?php

namespace GeekCms\PackagesManager\Providers;

use GeekCms\PackagesManager\Support\ServiceProvider as MainServiceProvider;
use Modules\Menu\Libs\Admin\AdminSidenav;

/**
 * Class InitServiceProvider.
 */
class InitServiceProvider extends MainServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function registerNavigation()
    {
        \Menu::create('admin.sidenav', function ($menu) {
            $menu->setPresenter(AdminSidenav::class);
            $menu->route('admin', $this->getPrefix().$this->getName().'::admin/sidenav.Dashboard', [], null, [
                'icon' => 'font-icon font-icon-dashboard',
            ]);
        });

        if ($adminSidenav = \Menu::instance('admin.sidenav')) {
            $adminSidenav->dropdown(
                $this->getNavname(),
                function ($sub) {
                    $sub->route($this->getAdminRoutePrefix().'packages', $this->getPrefix().$this->getName().'::admin/sidenav.installed', null, [
                        'icon' => 'font-icon font-icon-archive',
                    ]);

                    $sub->route($this->getAdminRoutePrefix().'packages.list', $this->getPrefix().$this->getName().'::admin/sidenav.lists', null, [
                        'icon' => 'font-icon font-icon-earth-bordered',
                    ]);
                },
                null,
                ['icon' => 'font-icon font-icon-github']
            );
        }
    }
}
