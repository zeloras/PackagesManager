<?php

namespace GeekCms\PackagesManager\Modules;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\ServiceProvider as MainServiceAbstract;



abstract class ModuleAbstract extends MainServiceAbstract
{
    use Macroable;

    /**
     * The laravel|lumen application instance.
     */
    protected $app;

    /**
     * The module name.
     *
     * @var
     */
    protected $name;

    /**
     * The module path.
     *
     * @var string
     */
    protected $path;

    /**
     * @var array of cached Json objects, keyed by filename
     */
    protected $moduleJson = [];

    /**
     * The constructor.
     *
     * @param Container $app
     * @param $name
     * @param $path
     */
    public function __construct(Container $app, $name, $path)
    {
        parent::__construct($app);
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * Get laravel instance.
     *
     */
    public function getLaravel()
    {
        return $this->app;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get name in lower case.
     *
     * @return string
     */
    public function getLowerName()
    {
        return strtolower($this->name);
    }

    /**
     * Get name in studly case.
     *
     * @return string
     */
    public function getStudlyName()
    {
        return Str::studly($this->name);
    }

    /**
     * Get alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->get('alias');
    }

    /**
     * Get module requirements.
     *
     * @return array
     */
    public function getRequires()
    {
        return $this->get('requires');
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->fireEvent('boot');
    }


    /**
     * Get json contents from the cache, setting as needed.
     *
     * @param string $file
     *
     * @return Json
     */
    public function json($file = null) : Json
    {
        $path = $this->getPath();
        if (empty($file)) {
            $file = 'module.json';
            $file_module = $this->getLowerName() . '_' . $file;
            $path_storage = storage_path(\Gcms::MODULES_STORAGE_DIR);
            $full_path = $path_storage . DIRECTORY_SEPARATOR . $file_module;
            if (!file_exists($full_path) || !is_file($full_path)) {
                copy($path . DIRECTORY_SEPARATOR . $file, $full_path);
            }

            $file = $file_module;
            $path = $path_storage;
        }

        return array_get($this->moduleJson, $file, function () use ($file, $path) {
            return $this->moduleJson[$file] = new Json($path . DIRECTORY_SEPARATOR . $file, $this->app['files']);
        });
    }

    /**
     * Get a specific data from json file by given the key.
     *
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->json()->get($key, $default);
    }

    /**
     * Register the module.
     */
    public function register()
    {
        $this->fireEvent('register');
    }

    /**
     * Register the module event.
     *
     * @param string $event
     */
    protected function fireEvent($event)
    {
        $this->app['events']->fire(sprintf('modules.%s.' . $event, $this->getLowerName()), [$this]);
    }

    /**
     * Handle call __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getStudlyName();
    }

    /**
     * Determine whether the given status same with the current module status.
     *
     * @param $status
     *
     * @return bool
     */
    public function isStatus($status) : bool
    {
        return $this->get('active', 0) === $status;
    }

    /**
     * Determine whether the current module activated.
     *
     * @return bool
     */
    public function enabled() : bool
    {
        return $this->isStatus(1);
    }

    /**
     *  Determine whether the current module not disabled.
     *
     * @return bool
     */
    public function disabled() : bool
    {
        return !$this->enabled();
    }

    /**
     * Set active state for current module.
     *
     * @param $active
     *
     * @return bool
     */
    public function setActive($active)
    {
        return $this->json()->set('active', $active)->save();
    }

    /**
     * Disable the current module.
     */
    public function disable()
    {
        $this->fireEvent('disabling');

        $this->setActive(0);

        $this->fireEvent('disabled');
    }

    /**
     * Enable the current module.
     */
    public function enable()
    {
        $this->fireEvent('enabling');

        $this->setActive(1);

        $this->fireEvent('enabled');
    }

    /**
     * Delete the current module.
     *
     * @return bool
     */
    public function delete()
    {
        return $this->json()->getFilesystem()->deleteDirectory($this->getPath());
    }

    /**
     * Handle call to __get method.
     *
     * @param $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
}
