<?php

namespace GeekCms\PackagesManager\Modules;

use GeekCms\PackagesManager\Support\Components\ChildServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Str;

class Module extends ChildServiceProvider
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
}
