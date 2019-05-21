<?php

namespace GeekCms\PackagesManager\Providers;

use GeekCms\PackagesManager\Support\ServiceProvider as MainServiceProvider;

/**
 * Class PackagesServiceProvider.
 */
class PackagesServiceProvider extends MainServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function registerNavigation()
    {
        if ($adminSidenav = \Menu::instance('admin.sidenav')) {
            $adminSidenav->dropdown(
                $this->getNavname(),
                function ($sub) {
                    $sub->route('admin.packages', trans($this->getPrefix() . $this->getName().'::admin/sidenav.installed'), null, [
                        'icon' => 'fa fa-fw fa-cogs',
                    ]);

                    $sub->route('admin.packages.list', trans($this->getPrefix() . $this->getName().'::admin/sidenav.lists'), null, [
                        'icon' => 'fa fa-fw fa-shopping-basket',
                    ]);
                },
                null,
                ['icon' => 'fa fa-fw fa-dropbox']
            );
        }
    }
}
