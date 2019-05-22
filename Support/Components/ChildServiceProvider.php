<?php

namespace GeekCms\PackagesManager\Support\Components;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Storage;
use GeekCms\PackagesManager\Repository\MainRepository;
use Nwidart\Modules\Module as MainModule;

/**
 * Class ChildServiceProvider.
 */
abstract class ChildServiceProvider extends MainModule
{
    /**
     * This name using for get path to modules from config file.
     */
    const PATH_MODULES = 'modules';

    /**
     * Base module name
     */
    protected $module_facade = null;

    /**
     * This name using for get path to root/resources from config file.
     */
    const PATH_RESOURCES = 'resources';

    /**
     * This name using for set log channel.
     */
    const LOGS_CHANNEL = 'modules';

    /**
     * Path for load config
     */
    const CONFIG_PATH = 'Config/config.php';

    /**
     * Main laravel $app
     *
     * @var null
     */
    protected $app = null;

    /**
     * Menu name.
     *
     * @var string
     */
    protected $navname = '';

    /**
     * Module name.
     *
     * @var string
     */
    protected $name = 'module';

    /**
     * Prefix for configs, settings etc.
     *
     * @var string
     */
    protected $prefix = 'module_';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Namespace module name.
     *
     * @var string
     */
    protected $namespace_name = 'Module';

    /**
     * Module path from root.
     *
     * @var string
     */
    protected $module_path = '';

    /**
     * Will contain module settings
     *
     * @var array
     */
    protected $module_config = [];

    /**
     * Config contain base paths for module components.
     *
     * @var array
     */
    protected static $components_path = [
        'modules' => self::PATH_MODULES,
        'resources' => self::PATH_RESOURCES,
        'main_lang' => 'lang/modules/',
        'main_view' => 'views/modules/',
        'module_routes' => 'Http/routes.php',
        'module_lang' => 'Resources/lang',
        'module_view' => 'Resources/views',
        'module_factories' => 'Database/factories',
        'module_migrations' => 'Database/Migrations'
    ];

    /**
     * Logs instance.
     *
     * @var
     */
    protected $module_logs;

    /**
     * Storage instances for work with filesystem in module dir.
     *
     * @var Storage
     */
    protected $module_storage_instance;

    /**
     * Storage instances for work with filesystem in root/resources dir.
     *
     * @var Storage
     */
    protected $resources_storage_instance;


    /**
     * ServiceProvider constructor.
     * @param Container $app
     * @throws \Exception
     */
    public function __construct(Container $app)
    {
        parent::__construct($app, $this->getName(), $this->getModulePath());
        $this->registerProviders();
        $this->registerAliases();
    }

    /**
     * Main boot init.
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerFactories();
        $this->registerMigrations();
        $this->registerBladeDirective();
        $this->registerViews();
        $this->registerNavigation();
    }

    /**
     * Method for register module.
     */
    public function register()
    {
        parent::register();

        if (!empty($this->getModuleFacade())) {
            $this->app->singleton($this->getModuleFacade(), function ($app) {
                $path = base_path('Modules');
                return new MainRepository($app, $path);
            });
        }
    }


    /**
     * Register menu item in admin sidebar.
     */
    public function registerNavigation()
    {
        if ($adminSidenav = \Menu::instance('admin.sidenav')) {
            $adminSidenav->route('admin.'.$this->getName(), $this->getNavname(), null, [
                'icon' => 'fa fa-fw fa-comments-o',
                'new' => 0,
            ]);
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langModulePath = $this->getModulePath() . self::$components_path['module_lang'];

        if ($this->is_exists(self::$components_path['module_lang'])) {
            $this->loadTranslationsFrom($langModulePath, $this->getPrefix() . $this->getName());
        }

        $this->setNavname(trans($this->getPrefix() . $this->getName().'::admin/sidenav.name'));
    }

    /**
     * Register routes.
     *
     * @throws \Exception
     */
    public function registerRoutes()
    {
        $path_routes = $this->getModulePath() . self::$components_path['module_routes'];
        if (!app()->routesAreCached()) {
            if ($this->is_exists(self::$components_path['module_routes'], ['is_file' => true])) {
                require_once $path_routes;
            }
        }
    }

    /**
     * Register views.
     *
     * @throws \Exception
     */
    public function registerViews()
    {
        $view_path_main = resource_path(self::$components_path['main_view'] . $this->getName());
        $view_path_module = $this->getModulePath() . self::$components_path['module_view'];

        if ($this->is_exists(self::$components_path['module_view'])) {
            $this->publishes([
                $view_path_module => $view_path_main,
            ], 'views');

            $this->loadViewsFrom(array_merge(array_map(function ($path) {
                return $path . self::$components_path['modules'] . \DIRECTORY_SEPARATOR . $this->getName();
            }, \Config::get('view.paths')), [$view_path_module]), $this->getName());
        }
    }

    /**
     * Registration module factories.
     *
     * @throws \Exception
     */
    public function registerFactories()
    {
        $factory_path = $this->getModulePath() . self::$components_path['module_factories'];

        if ($this->is_exists(self::$components_path['module_factories'])) {
            if (!app()->environment('production')) {
                app(Factory::class)->load($factory_path);
            }
        }
    }

    /**
     * For include helpers or something else
     *
     * @throws \Exception
     */
    public function registerFiles()
    {
        foreach ($this->get('files', []) as $file) {
            $path = base_path($this->getPath() . DIRECTORY_SEPARATOR . $file);
            if ($this->is_exists($path, ['is_file' => true])) {
                require $path;
            }
        }
    }

    /**
     * Load module migrations.
     *
     * @throws \Exception
     */
    public function registerMigrations()
    {
        $migration_path = $this->getModulePath() . self::$components_path['module_migrations'];
        if ($this->is_exists(self::$components_path['module_migrations'])) {
            $this->loadMigrationsFrom($migration_path);
        }
    }

    /**
     * Get current value for that module
     *
     * @param null $param
     * @param null $module_name
     * @return any|null
     */
    public function getConfig($param = null, $module_name = null)
    {
        $full_name = (!empty($module_name)) ? $module_name : $this->getPrefix() . $this->getName();
        if (\Config::has($full_name)) {
            $get_arr = \Config::get($full_name, null);
            return (!empty($param) && isset($get_arr[$param])) ? $get_arr[$param] : null;
        }

        return null;
    }

    /**
     * Registration blade directive.
     */
    public function registerBladeDirective() {}

    /**
     * Get all unresolved requirements which don't initialized
     *
     * @return array
     */
    public function getUnresolvedRequirements()
    {
        $requirements = [];
        $aliases = $this->getRequires();
        if ($aliases && count($aliases)) {
            foreach ($aliases as $requirementName) {
                $requirements[$requirementName] = $this->getApp()->isAlias($requirementName);
            }
        }

        return $requirements;
    }

    /**
     * @inheritDoc
     */
    public function registerAliases()
    {
        /**
         * Try load and set alias for "light" version, light version it's like a helper
         */
        $config = $this->getModuleConfig();
        if (isset($config['FacadeName']['alias']) && is_array($config['FacadeName'])) {
            try {
                $aliasName = $config['FacadeName']['alias'];
                $facadeClass = get_class(new $config['FacadeName']['facadePath']());
                $repoClass = new $config['FacadeName']['mainRepoPath']();

                if (!class_exists($aliasName)) {
                    $this->app->bind($aliasName, function ($app) use ($repoClass) {
                        return $repoClass::getInstance();
                    });
                    $this->app->instance(get_class($repoClass), $repoClass::getInstance());
                    class_alias($facadeClass, $aliasName);
                }
            } catch (\Exception $e) {
                $this->getModuleLogs()->error($e);
                throw new \Exception($e);
            }
        }

        $loader = AliasLoader::getInstance();
        foreach ($this->get('aliases', []) as $aliasName => $aliasClass) {
            $loader->alias($aliasName, $aliasClass);
        }
    }

    /**
     * @inheritDoc
     */
    public function registerProviders()
    {
        if (is_callable(array(parent::class, 'registerProviders'))) {
            parent::registerProviders();
        }
    }

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $this->getApp()->getCachedServicesPath();
    }

    /**
     * Function for check file.
     *
     * @param string $path
     * @param array  ...$args Can contain array
     *                        with key-value:
     *                        is_file bool false - Check $path is file
     *                        instance string 'module' - Available check disks: self::PATH_RESOURCES, self::PATH_MODULES
     *                        create_dir bool true - If we check only directory, we can try to create folder in process
     *                        exception bool false - If file/dir not exists, show exception
     *                        exception_message string - Custom message for exception
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function is_exists(string $path = '', array ...$args)
    {
        $is_file = false;
        $create_dir = false;
        $exception = false;
        $exception_message = 'File or directory not exists:'.$path;
        $that_file = $that_dir = $status = $instance_init = false;
        $instance = $this->getModuleStorageInstance();

        if (\count($args)) {
            foreach ($args[0] as $key => $value) {
                if ('is_file' === $key) {
                    $is_file = (bool) $value;
                } elseif ('exception' === $key) {
                    $exception = (bool) $value;
                } elseif ('create_dir' === $key) {
                    $create_dir = (bool) $value;
                } elseif ('exception_message' === $key) {
                    $exception_message = (string) $value;
                } elseif ('instance' === $key) {
                    $instance = (self::PATH_RESOURCES === $value) ? $this->getResourcesStorageInstance() : $instance;
                    $instance_init = (self::PATH_RESOURCES === $value);
                }
            }
        }

        $path = (!$instance_init) ? $this->getNamespaceName().\DIRECTORY_SEPARATOR.$path : $path;

        $exists = $instance->exists($path);

        if ($exists) {
            $mime = $instance->getMimetype($path);

            if ('directory' === $mime) {
                $that_dir = true;
            } else {
                $that_file = true;
            }
        } else {
            if ($create_dir && !$that_dir && !$that_file) {
                $that_dir = $instance->makeDirectory($path);
            }

            if (!$exception && !$that_dir) {
                return $status;
            }
        }

        $status = ($is_file && $that_file || !$is_file && $that_dir);

        if ($exception && (!$exists || !$status)) {
            $this->getModuleLogs()->error($exception_message);

            throw new \Exception($exception_message);
        }

        return $status;
    }
}
