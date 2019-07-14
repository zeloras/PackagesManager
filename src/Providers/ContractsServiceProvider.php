<?php

namespace GeekCms\PackagesManager\Providers;

use Illuminate\Support\ServiceProvider;
use GeekCms\PackagesManager\Repository\RepositoryInterface;
use Nwidart\Modules\Laravel\LaravelFileRepository;

class ContractsServiceProvider extends ServiceProvider
{
    /**
     * Register some binding.
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, LaravelFileRepository::class);
    }
}
