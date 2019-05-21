<?php

namespace GeekCms\PackagesManager\Support;

use BadMethodCallException;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GeekCms\PackagesManager\Support\Components\ChildServiceProvider;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends ChildServiceProvider
{
    /**
     * ServiceProvider constructor.
     * @param Container $app
     * @throws \Exception
     */
    public function __construct(Container $app)
    {
        $this->setApp($app);
        $this->loadCoreComponents();
        $this->initVariables();
        $this->registerConfig();
        $this->registerFiles();

        parent::__construct($app, $this->getName(), $this->getModulePath());
    }

    /**
     * Main boot init.
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Method for register module.
     */
    public function register()
    {
        $this->getUnresolvedRequirements();
        parent::register();
    }

    /**
     * Init main module data, like a name or root path.
     *
     * @throws \Exception
     */
    private function initVariables()
    {
        try {
            preg_match_all('/([^\\\]+\\\){1}(?<module>.*?)\\\/ims', static::class, $module_names);
            $this->setNamespaceName((isset($module_names['module'][0])) ? $module_names['module'][0] : $this->getNamespaceName());
            $this->setName(strtolower($this->getNamespaceName()));
            $this->setPath($this->getModuleStorageInstance()->path($this->getNamespaceName()).\DIRECTORY_SEPARATOR);
            $this->setModulePath($this->getPath());
        } catch (\Exception $e) {
            $this->getModuleLogs()->error($e);
            throw new \Exception($e.'Look at the provider, something wrong with get module path or init class variables');
        }
    }

    /**
     * Load bases components for work with module
     */
    private function loadCoreComponents()
    {
        if (!$this->getModuleLogs() instanceof Log) {
            $this->setModuleLogs(Log::channel($this::LOGS_CHANNEL));
        }

        if (!$this->getModuleStorageInstance() instanceof Storage) {
            $this->setModuleStorageInstance(Storage::disk($this::PATH_MODULES));
        }

        if (!$this->getResourcesStorageInstance() instanceof Storage) {
            $this->setResourcesStorageInstance(Storage::disk($this::PATH_RESOURCES));
        }
    }

    /**
     * Registration module config.
     *
     * @throws \Exception
     */
    public function registerConfig()
    {
        $this->setModuleFacade(null);
        $config_path = $this->getModulePath() . $this::CONFIG_PATH;

        if ($this->is_exists($this::CONFIG_PATH, ['is_file' => true])) {
            $load_config = require_once $config_path;

            $this->publishes([
                $config_path => config_path($this->getPrefix().$this->getName().'.php'),
            ], 'config');

            if (!empty($load_config) && isset($load_config['FacadeName'])) {
                $this->setModuleFacade($load_config['FacadeName']);
            }

            $this->mergeConfigFrom(
                $config_path,
                $this->getPrefix().$this->getName()
            );
        }
    }

    /**
     * Getter/setter for varchars
     *
     * @param null $variable
     * @param array $params
     * @return mixed
     */
    public function __call($variable = null, $params = []) {
        $filter = preg_replace('/^get|^set/imus', '', $variable);
        $filter_under = preg_replace_callback('/_([^_]+)/imus', function ($m) {
            return ucfirst($m[1]);
        }, $filter);

        $filter_upper = preg_replace_callback('/([A-Z]{1})/mus', function ($m) {
            return '_'.lcfirst($m[1]);
        }, $filter);

        $filter_upper = preg_replace('/^_/', '', $filter_upper);

        if (property_exists(self::class, $filter_under) || property_exists(self::class, $filter_upper)) {
            $filter = (property_exists(self::class, $filter_under)) ? $filter_under : $filter_upper;

            if (count($params)) {
                $this->$filter = $params[array_keys($params)[0]];
            }

            return $this->$filter;
        }

        if (!method_exists(self::class, $variable)) {
            throw new BadMethodCallException("Method {$variable} does not exist.");
        }
    }
}
