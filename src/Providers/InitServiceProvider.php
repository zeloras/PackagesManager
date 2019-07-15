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
     *
     * @param string|null $name
     * @return mixed|void
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
        $this->app->singleton(RepositoryInterface::class, static function ($app) {
            return new MainRepository($app, config('modules.paths.modules'));
        });
        $this->app->alias(RepositoryInterface::class, config('modules.registration_name', 'modules'));
        //$this->registerFacades();
    }

    /**
     * {@inheritdoc}
     */
    public function registerNavigation(): void
    {
        $self = $this;
        Menu::create('admin.sidenav', static function ($menu) use ($self) {
            $menu->setPresenter(AdminSidenav::class);
            $menu->route('admin', $self->getPrefix() . $self->getName() . '::admin/sidenav.Dashboard', [], null, [
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
        return [RepositoryInterface::class, config('modules.registration_name', 'modules')];
    }
}
