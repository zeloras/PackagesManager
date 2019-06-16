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

        parent::registerNavigation();
    }
}
