<?php


namespace GeekCms\PackagesManager\Support;


use BadMethodCallException;
use Config;
use Gcms;
use GeekCms\PackagesManager\Providers\BootstrapServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Validator;
use Menu;
use GeekCms\PackagesManager\Modules\ModuleAbstract;
use Log;
use Storage;
use PackageSystem;
use function call_user_func_array;
use function count;
use const DIRECTORY_SEPARATOR;

/**
 * Class MainServiceAbstract.
 *
 * @method Container getApp()
 * @method Container setApp(Container $app)
 * @method string    getNavname()
 * @method string    setNavname($name)
 * @method string    getModuleFacade()
 * @method string    setModuleFacade($name)
 * @method string    getName()
 * @method string    setName($name)
 * @method string    getPrefix()
 * @method string    setPrefix($prefix)
 * @method string    getAdminRoutePrefix()
 * @method string    setAdminRoutePrefix($prefix)
 * @method string    getDefer()
 * @method string    setDefer(bool $status)
 * @method string    getNamespaceName()
 * @method string    setNamespaceName($name)
 * @method string    getModulePath()
 * @method string    setModulePath($path)
 * @method string    getPath()
 * @method string    setPath($path)
 * @method string    getModuleConfig()
 * @method string    setModuleConfig(array $config)
 * @method string    getModuleLogs()
 * @method string    setModuleLogs(string $name)
 * @method string    getModuleStorageInstance()
 * @method string    setModuleStorageInstance(Storage $name)
 * @method string    getResourcesStorageInstance()
 * @method string    setResourcesStorageInstance(Storage $name)
 */
abstract class MainServiceAbstract extends ModuleAbstract implements MainServiceRegistrationInterface, MainServiceInterface
{
    /**
     * Config contain base paths for module components.
     *
     * @var array
     */
    public static $components_path = [
        'modules' => self::PATH_MODULES,
        'resources' => self::PATH_RESOURCES,
        'main_lang' => self::PATH_SRC . 'lang/modules/',
        'main_view' => self::PATH_SRC . 'views/modules/',
        'module_routes' => self::PATH_SRC . 'Http/routes.php',
        'module_lang' => self::PATH_SRC . 'Resources/lang',
        'module_view' => self::PATH_SRC . 'Resources/views',
        'module_factories' => self::PATH_SRC . 'Database/factories',
        'module_migrations' => self::PATH_SRC . 'Database/Migrations',
        'rules_map' => 'Models\\Validators\\Rules',
    ];

    /**
     * Base module name.
     *
     * @var null|string
     */
    protected $module_facade;

    /**
     * Main laravel $app.
     *
     * @var null|object
     */
    protected $app;

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
    protected $prefix = '';

    /**
     * Prefix for admin routes.
     *
     * @var string
     */
    protected $admin_route_prefix = 'admin.';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
     * @internal
     */
    protected $path;

    /**
     * Will contain module settings.
     *
     * @var array
     */
    protected $module_config = [];

    /**
     * Logs instance.
     *
     * @var string
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
     * @inheritDoc
     */
    public function __construct(Container $app, $name = null, $path = null)
    {
        if (!empty($name) && !empty($path)) {
            $this->setApp($app);
            $this->setPrefix(Gcms::MODULES_PREFIX);
            $this->loadCoreComponents($name);
            $this->initVariables($this->getName(), $path);
        }

        parent::__construct($app, $name, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadCoreComponents();
        $this->initVariables($this->getName(), $this->getPath());

        $this->registerConfig();
        $this->registerFiles();
        $this->registerAliases();
        $this->registerProviders();
        $this->registerFacades();

        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerFactories();
        $this->registerMigrations();
        $this->registerBladeDirective();
        $this->registerViews();
        $this->registerValidationRules();
    }
    /**
     * {@inheritdoc}
     */
    public function register(string $main_name = null)
    {
        $this->loadCoreComponents($main_name);
        $this->initVariables($this->getName(), $this->getPath());

        $this->registerConfig();
        $this->registerFiles();
        $this->registerAliases();
        $this->registerProviders();
        $this->registerFacades();

        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerFactories();
        $this->registerMigrations();
        $this->registerBladeDirective();
        $this->registerViews();
        $this->registerNavigation();
        $this->registerValidationRules();
    }


    /**
     * {@inheritdoc}
     */
    public function loadCoreComponents(string $main_name = null): void
    {
        $preg_fnc = static function ($value) {
            return preg_replace('/' . preg_quote(base_path(), DIRECTORY_SEPARATOR) . '\\/|\\/\*$/uims', '', $value);
        };
        $this->setName(empty($main_name) ? $this->getName() : strtolower($main_name));
        $disk_name = $this::PATH_MODULES;

        if (class_exists('PackageSystem')) {
            $module_path = PackageSystem::getModulePath($this->getName());
            $scaned_paths = array_map(static function ($val) use ($module_path, $preg_fnc) {
                $preg_path = $preg_fnc($val);
                $preg_module = $preg_fnc(dirname($module_path, self::PARENT_LEVEL_DIR));
                return ($preg_path === $preg_module) ? strtolower($preg_module) : null;
            }, PackageSystem::getScanPaths());

            $real_path = array_filter($scaned_paths, static function ($value) {
                return !empty($value);
            });

            if (count($real_path)) {
                $disk_name_first = array_first($real_path);
                self::$components_path['modules'] = $disk_name_first;
                $disk_name = isset($this->app['config']["filesystems.disks.{$disk_name_first}"]) ? $disk_name_first : $disk_name;
            }
        }

        if (!$this->getModuleLogs() instanceof Log) {
            $this->setModuleLogs(Log::channel($this::LOGS_CHANNEL));
        }

        if (class_exists('Storage')) {
            if (!$this->getModuleStorageInstance() instanceof Storage) {
                $this->setModuleStorageInstance(Storage::disk($disk_name));
            }

            if (!$this->getResourcesStorageInstance() instanceof Storage) {
                $this->setResourcesStorageInstance(Storage::disk($disk_name));
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function initVariables(string $name = null, string $path = null): void
    {
        try {
            if (empty($name) && empty($path)) {
                preg_match_all('/([^\\\]+\\\){1}(?<module>.*?)\\\/ims', static::class, $module_names);
            } else {
                preg_match_all('/(?<module>' . $name . ')$/ims', $path, $module_names);
            }

            $this->setNamespaceName($module_names['module'][0] ?? $this->getNamespaceName());
            $this->setPath($this->getModuleStorageInstance()->path($this->getNamespaceName()) . DIRECTORY_SEPARATOR);
            $this->setName(strtolower($this->getNamespaceName()));
            $this->setModulePath($this->getPath());
            $this->setNavname($this->getPrefix() . $this->getName() . '::');
        } catch (Exception $e) {
            $this->getModuleLogs()->error($e);
            throw new Exception($e);
        }
    }

    /**
     * Getter/setter for variables class.
     *
     * @param null $variable
     * @param array $params
     * @throws BadMethodCallException
     *
     * @return mixed
     */
    public function __call($variable = null, $params = [])
    {
        $filter = preg_replace('/^get|^set/', '', $variable);
        $filter_under = preg_replace_callback('/_([^_]+)/mu', function ($m) {
            return ucfirst($m[1]);
        }, $filter);

        $filter_upper = preg_replace_callback('/([A-Z]{1})/mu', function ($m) {
            return '_' . lcfirst($m[1]);
        }, $filter);

        $filter_upper = preg_replace('/^_/', '', $filter_upper);

        if ((!empty($filter_under) && property_exists(self::class, $filter_under)) || (!empty($filter_upper) && property_exists(self::class, $filter_upper))) {
            $filter = property_exists(self::class, $filter_under) ? $filter_under : $filter_upper;

            if (count($params) && 0 === strpos($variable, 'set')) {
                $this->{$filter} = $params[array_keys($params)[0]];
            }

            return $this->{$filter};
        }

        if (!method_exists(self::class, $variable)) {
            throw new BadMethodCallException("Method {$variable} does not exist.");
        }

        return call_user_func_array($variable, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function registerConfig(): void
    {
        $config_path = $this->getModulePath() . $this::CONFIG_PATH;
        if ($this->is_exists($this::CONFIG_PATH, ['is_file' => true])) {
            $this->publishes([
                $config_path => config_path($this->getPrefix() . $this->getName() . '.php'),
            ], 'config');

            $this->mergeConfigFrom(
                $config_path,
                $this->getPrefix() . $this->getName()
            );

            $module_config = Config::get($this->getPrefix() . $this->getName(), []);

            if (!empty($module_config)) {
                $this->setModuleConfig($module_config);
                $module_config = null;
            }
        }
    }

    /**
     * Function for check file.
     *
     * @param string $path
     * @param array ...$args
     *                        with key-value:
     *                        is_file bool false - Check $path is file
     *                        instance string 'module' - Available check disks: self::PATH_RESOURCES, self::PATH_MODULES
     *                        create_dir bool true - If we check only directory, we can try to create folder in process
     *                        exception bool false - If file/dir not exists, show exception
     *                        exception_message string - Custom message for exception
     *
     * @return bool
     * @throws Exception
     */
    protected function is_exists(string $path = '', array ...$args): bool
    {
        $is_file = false;
        $create_dir = false;
        $exception = false;
        $exception_message = 'File or directory not exists:' . $path;
        $that_file = $that_dir = $status = $instance_init = false;
        $instance = $this->getModuleStorageInstance();

        if (count($args)) {
            foreach ($args[0] as $key => $value) {
                if ('is_file' === $key) {
                    $is_file = (bool)$value;
                } elseif ('exception' === $key) {
                    $exception = (bool)$value;
                } elseif ('create_dir' === $key) {
                    $create_dir = (bool)$value;
                } elseif ('exception_message' === $key) {
                    $exception_message = (string)$value;
                } elseif ('instance' === $key) {
                    $instance = (self::PATH_RESOURCES === $value) ? $this->getResourcesStorageInstance() : $instance;
                    $instance_init = (self::PATH_RESOURCES === $value);
                }
            }
        }

        $path = (!$instance_init) ? $this->getNamespaceName() . DIRECTORY_SEPARATOR . $path : $path;

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

        $status = ($is_file && $that_file) || (!$is_file && $that_dir);

        if ($exception && (!$exists || !$status)) {
            $this->getModuleLogs()->error($exception_message);

            throw new Exception($exception_message);
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function registerFiles(): void
    {
        try {
            $files = $this->get('files', []);
        } catch (Exception $e) {
            $files = [];
        }

        foreach ($files as $file) {
            $path = base_path($this->getPath() . DIRECTORY_SEPARATOR . $file);

            if ($this->is_exists($path, ['is_file' => true])) {
                require $path;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerAliases(): void
    {
        try {
            $aliases = $this->get('aliases', []);
        } catch (Exception $e) {
            $aliases = [];
        }

        $loader = AliasLoader::getInstance();
        foreach ($aliases as $aliasName => $aliasClass) {
            if (!class_exists($aliasName)) {
                $loader->alias($aliasName, $aliasClass);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerProviders(): void {}

    /**
     * {@inheritdoc}
     */
    public function registerTranslations(): void
    {
        $langModulePath = $this->getModulePath() . self::$components_path['module_lang'];

        if ($this->is_exists(self::$components_path['module_lang'])) {
            $this->loadTranslationsFrom($langModulePath, $this->getPrefix() . $this->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerRoutes(): void
    {
        $path_routes = $this->getModulePath() . self::$components_path['module_routes'];
        if (!app()->routesAreCached()) {
            if ($this->is_exists(self::$components_path['module_routes'], ['is_file' => true])) {
                require_once $path_routes;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerFactories(): void
    {
        $factory_path = $this->getModulePath() . self::$components_path['module_factories'];

        if ($this->is_exists(self::$components_path['module_factories']) && !app()->environment('production')) {
            app(Factory::class)->load($factory_path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerMigrations(): void
    {
        $migration_path = $this->getModulePath() . self::$components_path['module_migrations'];
        if ($this->is_exists(self::$components_path['module_migrations'])) {
            $this->loadMigrationsFrom($migration_path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerBladeDirective(): void {}

    /**
     * {@inheritdoc}
     */
    public function registerViews(): void
    {
        $view_path_main = resource_path(self::$components_path['main_view'] . $this->getName());
        $view_path_module = $this->getModulePath() . self::$components_path['module_view'];

        if ($this->is_exists(self::$components_path['module_view'])) {
            $this->publishes([
                $view_path_module => $view_path_main,
            ], 'views');

            $this->loadViewsFrom(array_merge(array_map(function ($path) {
                return $path . self::$components_path['modules'] . DIRECTORY_SEPARATOR . $this->getName();
            }, Config::get('view.paths')), [$view_path_module]), $this->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerValidationRules(): void
    {
        $base_namespace = ucfirst(self::PATH_MODULES) . '\\' . $this->getNamespaceName() . '\\' . self::$components_path['rules_map'];
        if (class_exists($base_namespace)) {
            Validator::resolver(static function ($translator, $data, $rules, $messages) use ($base_namespace) {
                return new $base_namespace($translator, $data, $rules, $messages);
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerNavigation(): void
    {
        $menu = $this->getMenu();

        if ($adminSidenav = Menu::instance('admin.sidenav')) {
            if ($menu && count($menu)) {
                foreach ($menu as $menu_item) {
                    $route = !empty($menu_item['route']) ? $menu_item['route'] : $this->getName();
                    $icon = !empty($menu_item['icon']) ? $menu_item['icon'] : 'fa fa-fw fa-comments-o';
                    $name = !empty($menu_item['i18n_name']) ? $menu_item['i18n_name'] : 'admin/sidenav.name';

                    if (isset($menu_item['child']) && count($menu_item['child'])) {
                        $adminSidenav->dropdown(
                            $this->getNavname() . $name,
                            function ($sub) use ($menu_item) {
                                foreach ($menu_item['child'] as $menu_child) {
                                    $route = !empty($menu_child['route']) ? $menu_child['route'] : $this->getName();
                                    $icon = !empty($menu_child['icon']) ? $menu_child['icon'] : 'fa fa-fw fa-comments-o';
                                    $name = !empty($menu_child['i18n_name']) ? $menu_child['i18n_name'] : 'admin/sidenav.name';

                                    $sub->route($this->getAdminRoutePrefix() . $route, $this->getNavname() . $name, null, [
                                        'icon' => $icon,
                                    ]);
                                }
                            },
                            null,
                            ['icon' => $icon]
                        );
                    } elseif (!isset($menu_item['child']) || !count($menu_item['child'])) {
                        $adminSidenav->route($this->getAdminRoutePrefix() . $route, $this->getNavname() . $name, null, [
                            'icon' => $icon,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function registerFacades(): void
    {
        /**
         * Try load and set alias for "light" version, light version it's like a helper.
         */
        $config = $this->getModuleConfig();
        $loader = AliasLoader::getInstance();
        if (is_array($config) && isset($config['FacadeName']['alias'])) {

            try {
                $path = base_path(ucfirst(self::PATH_MODULES));
                $aliasName = $config['FacadeName']['alias'];
                $facadeClass = get_class(new $config['FacadeName']['facadePath']());
                $repoClass = $config['FacadeName']['mainRepoPath'];

                if (!class_exists($aliasName)) {
                    if (method_exists($repoClass, 'getInstance')) {
                        $this->app->bind($aliasName, static function ($app) use ($repoClass) {
                            return (new $repoClass())::getInstance();
                        });

                        $this->app->instance(get_class(new $repoClass()), $repoClass::getInstance());
                        $loader->alias($facadeClass, $aliasName);
                        class_alias($facadeClass, $aliasName);
                    } else {
                        $this->app->bind($aliasName, static function ($app) use ($repoClass, $path) {
                            return new $repoClass($app, $path);
                        });

                        $this->app->singleton($aliasName, static function ($app) use ($repoClass, $path) {
                            return new $repoClass($app, $path);
                        });
                        $loader->alias($facadeClass, $aliasName);
                        class_alias($facadeClass, $aliasName);
                    }
                }
            } catch (Exception $e) {
                $this->getModuleLogs()->error($e);

                throw new Exception($e);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUnresolvedRequirements(): array
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
     * {@inheritdoc}
     */
    public function getCachedServicesPath(): string
    {
        return $this->getApp()->getCachedServicesPath();
    }

    /**
     * Get menu data.
     *
     * @return array
     */
    public function getMenu(): array
    {
        return $this->get('menu_sidebar', []);
    }

    /**
     * Register all modules.
     */
    protected function registerModules()
    {
        $this->app->register(BootstrapServiceProvider::class);
    }

    /**
     * Registration module namespaces
     */
    protected function registerNamespaces()
    {
        $configPath = dirname(__DIR__, self::PARENT_LEVEL_DIR) . '/Config/modules.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'modules');
            $this->publishes([
                $configPath => config_path('modules.php'),
            ], 'config');
        }
    }
}