<?php

namespace GeekCms\PackagesManager\Support;

use Config;
use GeekCms\PackagesManager\Providers\MainServiceProviderInterface;
use function is_array;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends MainServiceProvider  implements MainServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function registerConfig(): void
    {
        parent::registerConfig();
        $module_config = Config::get($this->getPrefix() . $this->getName(), []);
        if (isset($module_config['FacadeName']) && !is_array($module_config['FacadeName'])) {
            $this->setModuleFacade($module_config['FacadeName']);
        }

        if (!empty($module_config)) {
            $this->setModuleConfig($module_config);
            $module_config = null;
        }
    }

    /**
     * @inheritDoc
     */
    public static function mainInit(array ...$args)
    {
        $instance = static::class;
        if (class_exists($instance)) {
            $instance = new $instance(...$args[0]);
            call_user_func_array([$instance, 'register'], []);
        } else {
            $instance = null;
        }

        return $instance;
    }
}
