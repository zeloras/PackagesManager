<?php

namespace GeekCms\PackagesManager\Support;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider as MainServiceProvider;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends MainServiceProvider
{
    /**
     * This name using for get path to modules from config file.
     */
    const PATH_MODULES = 'modules';

    /**
     * This name using for get path to root/resources from config file.
     */
    const PATH_RESOURCES = 'resources';

    /**
     * This name using for set log channel.
     */
    const LOGS_CHANNEL = 'modules';

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
     * Config contain base paths for module components.
     *
     * @var array
     */
    protected static $components_path = [
        'routes' => 'Http/routes.php',
        'modules' => 'modules',
        'main_lang' => 'lang/modules/',
        'main_view' => 'views/modules/',
        'module_lang' => 'Resources/lang',
        'module_view' => 'Resources/views',
        'module_factories' => 'Database/factories',
        'module_migrations' => 'Database/Migrations',
        'module_config' => 'Config/config.php',
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
    private $module_storage_instance;

    /**
     * Storage instances for work with filesystem in root/resources dir.
     *
     * @var Storage
     */
    private $resources_storage_instance;

    /**
     * Main boot init.
     */
    public function boot()
    {
        $this->initVariables();
        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerFactories();
        $this->loadMigrations();
        $this->registerBladeDirective();
        $this->registerViews();
        $this->registerNavigation();
    }

    /**
     * Method for register module.
     */
    public function register()
    {
    }

    /**
     * Register menu item in admin sidebar.
     */
    public function registerNavigation()
    {
        if ($adminSidenav = \Menu::instance('admin.sidenav')) {
            $adminSidenav->route('admin.'.$this->name, $this->navname, null, [
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
        $langPath = resource_path(self::$components_path['main_lang'].$this->name);

        if ($this->is_exists()) {
            if (is_dir($langPath)) {
                $this->loadTranslationsFrom($langPath, $this->prefix.$this->name);
            } else {
                $this->loadTranslationsFrom($this->module_path.self::$components_path['module_lang'], $this->prefix.$this->name);
            }
        }

        $this->navname = trans($this->prefix.$this->name.'::admin/sidenav.name');
    }

    /**
     * Register routes.
     *
     * @throws \Exception
     */
    public function registerRoutes()
    {
        $path_routes = $this->module_path.self::$components_path['routes'];

        if (!app()->routesAreCached()) {
            if ($this->is_exists(self::$components_path['routes'], ['is_file' => true])) {
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
        $view_path_main = resource_path(self::$components_path['main_view'].$this->name);
        $view_path_module = $this->module_path.self::$components_path['module_view'];

        if ($this->is_exists(self::$components_path['module_view'])) {
            $this->publishes([
                $view_path_module => $view_path_main,
            ], 'views');

            $this->loadViewsFrom(array_merge(array_map(function ($path) {
                return $path.self::$components_path['modules'].\DIRECTORY_SEPARATOR.$this->name;
            }, \Config::get('view.paths')), [$view_path_module]), $this->name);
        }
    }

    /**
     * Registration module factories.
     *
     * @throws \Exception
     */
    public function registerFactories()
    {
        $factory_path = $this->module_path.self::$components_path['module_factories'];

        if ($this->is_exists(self::$components_path['module_factories'])) {
            if (!app()->environment('production')) {
                app(Factory::class)->load($factory_path);
            }
        }
    }

    /**
     * Load module migrations.
     *
     * @throws \Exception
     */
    public function loadMigrations()
    {
        $migration_path = $this->module_path.self::$components_path['module_migrations'];

        if ($this->is_exists(self::$components_path['module_migrations'])) {
            $this->loadMigrationsFrom($migration_path);
        }
    }

    /**
     * Registration module config.
     *
     * @throws \Exception
     */
    public function registerConfig()
    {
        $config_path = $this->module_path.self::$components_path['module_config'];

        if ($this->is_exists(self::$components_path['module_config'], ['is_file' => true])) {
            $this->publishes([
                $config_path => config_path($this->prefix.$this->name.'.php'),
            ], 'config');

            $this->mergeConfigFrom(
                $config_path,
                $this->prefix.$this->name
            );
        }
    }

    /**
     * Registration blade directive.
     */
    public function registerBladeDirective()
    {
    }

    /**
     * Init main module data, like a name or root path.
     *
     * @throws \Exception
     */
    private function initVariables()
    {
        if (!$this->module_logs instanceof Log) {
            $this->module_logs = Log::channel(self::LOGS_CHANNEL);
            $this->module_logs->info('Init "'.self::LOGS_CHANNEL.'" channel for logs!');
        }

        try {
            if (!$this->module_storage_instance instanceof Storage) {
                $this->module_storage_instance = Storage::disk(self::PATH_MODULES);
            }

            if (!$this->resources_storage_instance instanceof Storage) {
                $this->resources_storage_instance = Storage::disk(self::PATH_RESOURCES);
            }

            preg_match_all('/([^\\\]+\\\){1}(?<module>.*?)\\\/ims', static::class, $module_names);
            $this->namespace_name = (isset($module_names['module'][0])) ? $module_names['module'][0] : $this->namespace_name;
            $this->name = strtolower($this->namespace_name);
            $this->module_path = $this->module_storage_instance->path($this->namespace_name).\DIRECTORY_SEPARATOR;
        } catch (\Exception $e) {
            $this->module_logs->error($e);

            throw new \Exception($e.'Look at the provider, something wrong with get module path or init class variables');
        }
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
    private function is_exists(string $path = '', array ...$args)
    {
        $is_file = false;
        $create_dir = true;
        $exception = false;
        $exception_message = 'File or directory not exists:'.$path;
        $that_file = $that_dir = $status = $instance_init = false;
        $instance = $this->module_storage_instance;

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
                    $instance = (self::PATH_RESOURCES === $value) ? $this->resources_storage_instance : $instance;
                    $instance_init = (self::PATH_RESOURCES === $value);
                }
            }
        }

        $path = (!$instance_init) ? $this->namespace_name.\DIRECTORY_SEPARATOR.$path : $path;

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
            $this->module_logs->error($exception_message);

            throw new \Exception($exception_message);
        }

        return $status;
    }
}
