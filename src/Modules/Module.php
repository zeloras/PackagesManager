<?php

namespace GeekCms\PackagesManager\Modules;

class Module extends ModuleAbstract
{
    /**
     * For call all module providers in one "template"
     *
     * @return |null
     */
    public function registerInit()
    {
        $provider_called = null;

        $providers = $this->get('providers', []);
        foreach ($providers as $provider) {
            $main_provider = $provider::mainInit([$this->getLaravel(), $this->getName(), $this->getPath()]);
            if (!empty($main_provider)) {
                $provider_called = $main_provider;
            }
        }

        return $provider_called;
    }
}
