<?php

namespace Modules\Packages\Providers;

use App\Support\ServiceProvider;

/**
 * Class PackagesServiceProvider
 * @package Modules\Packages\Providers
 *
 * Package manager, for install and search new modules
 *
 */
class PackagesServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function registerNavigation()
    {
        if ($adminSidenav = \Menu::instance('admin.sidenav')) {
            $adminSidenav->dropdown(
                $this->navname,
                function ($sub) {
                    $sub->route('admin.packages', trans($this->prefix . $this->name . '::admin/sidenav.installed'), null, [
                        'icon' => 'fa fa-fw fa-cogs',
                    ]);

                    $sub->route('admin.packages.list', trans($this->prefix . $this->name . '::admin/sidenav.lists'), null, [
                        'icon' => 'fa fa-fw fa-shopping-basket',
                    ]);
                },
                null,
                ['icon' => 'fa fa-fw fa-dropbox']
            );
        }
    }
}
