<?php

namespace GeekCms\PackagesManager\Modules;

use GeekCms\PackagesManager\Support\MainServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Str;

class Module extends ModuleAbstract
{
    /**
     * {@inheritdoc}
     */
    public function registerProviders()
    {
        (new ProviderRepository($this->app, new Filesystem(), $this->getCachedServicesPath()))
            ->load($this->get('providers', []));
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedServicesPath()
    {
        return Str::replaceLast('services.php', $this->getSnakeName() . '_module.php', $this->app->getCachedServicesPath());
    }

    /**
     * {@inheritdoc}
     */
    public function registerAliases()
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->get('aliases', []) as $aliasName => $aliasClass) {
            $loader->alias($aliasName, $aliasClass);
        }
    }

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
