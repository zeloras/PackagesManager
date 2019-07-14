<?php

namespace GeekCms\PackagesManager\Providers;

use GeekCms\Menu\Libs\Admin\AdminSidenav;
use GeekCms\PackagesManager\Repository\MainRepository;
use GeekCms\PackagesManager\Repository\RepositoryInterface;
use GeekCms\PackagesManager\Support\ServiceProvider as MainServiceProvider;
use Illuminate\Container\Container;
use Menu;

/**
 * Class InitServiceProvider.
 */
class InitServiceProvider extends MainServiceProvider
{
    public function __construct(Container $app, $name = null, $path = null)
    {
        $this->setApp($app);
        parent::__construct($app, $name, $path);
    }

    /**
     * Booting the package.
     */
    public function boot()
    {
        if (!empty($this->getName()) && !empty($this->getPath())) {
            parent::boot();
        } else {
            $this->registerNamespaces();
            $this->registerModules();
        }
    }
    /**
     * Register the service provider.
     */
    public function register(string $name = null)
    {
        if (!empty($this->getName()) && !empty($this->getPath())) {
            parent::register($name);
        } else {
            $this->registerServices();
            $this->registerProviders();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices()
    {
        $this->app->singleton(RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('modules.paths.modules');
            return new MainRepository($app, $path);
        });
        $this->app->alias(RepositoryInterface::class, 'modules');
        //$this->registerFacades();
    }

    /**
     * {@inheritdoc}
     */
    public function registerNavigation(): void
    {
        Menu::create('admin.sidenav', function ($menu) {
            $menu->setPresenter(AdminSidenav::class);
            $menu->route('admin', $this->getPrefix() . $this->getName() . '::admin/sidenav.Dashboard', [], null, [
                'icon' => 'font-icon font-icon-dashboard',
            ]);
        });

        parent::registerNavigation();
    }

    /**
     * @inheritDoc
     */
    public function provides()
    {
        return [RepositoryInterface::class, 'modules'];
    }
}
