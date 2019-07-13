<?php

namespace GeekCms\PackagesManager\Providers;

use GeekCms\Menu\Libs\Admin\AdminSidenav;
use GeekCms\PackagesManager\Support\ServiceProvider as MainServiceProvider;
use Menu;

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
        Menu::create('admin.sidenav', function ($menu) {
            $menu->setPresenter(AdminSidenav::class);
            $menu->route('admin', $this->getPrefix() . $this->getName() . '::admin/sidenav.Dashboard', [], null, [
                'icon' => 'font-icon font-icon-dashboard',
            ]);
        });

        parent::registerNavigation();
    }
}
