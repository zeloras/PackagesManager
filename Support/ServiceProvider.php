<?php

namespace GeekCms\PackagesManager\Support;

use GeekCms\PackagesManager\Support\Components\ChildServiceProvider;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends ChildServiceProvider
{
    public function registerConfig()
    {
        parent::registerConfig();
        $module_config = \Config::get($this->getPrefix().$this->getName(), []);
        if (isset($module_config['FacadeName']) && !\is_array($module_config['FacadeName'])) {
            $this->setModuleFacade($module_config['FacadeName']);
        }

        if (!empty($module_config)) {
            $this->setModuleConfig($module_config);
            $module_config = null;
        }
    }
}
