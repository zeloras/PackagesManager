<?php


namespace GeekCms\PackagesManager\Support;


use Illuminate\Container\Container;

interface MainServiceRegistrationInterface
{
    /**
     * This name using for set log channel.
     */
    public const LOGS_CHANNEL = 'modules';

    /**
     * MainServiceInterface constructor.
     * @param Container $app
     * @param null $name
     * @param null $path
     */
    public function __construct(Container $app, $name = null, $path = null);

    /**
     * Boot method
     *
     * @return mixed
     */
    public function boot();

    /**
     * Registration method
     *
     * @param string|null $main_name
     * @return mixed
     */
    public function register(string $main_name = null);

    /**
     * Register main package config(s)
     *
     * @return void
     */
    public function registerConfig(): void;

    /**
     * Register package files(helpers for example)
     */
    public function registerFiles(): void;

    /**
     * Register package aliases
     */
    public function registerAliases(): void;

    /**
     * Register package providers
     */
    public function registerProviders(): void;

    /**
     * Register package facades
     */
    public function registerFacades(): void;

    /**
     * Register package languages files
     */
    public function registerTranslations(): void;

    /**
     * Register package routes
     */
    public function registerRoutes(): void;

    /**
     * Register package factories
     */
    public function registerFactories(): void;

    /**
     * Register package migrations
     */
    public function registerMigrations(): void;

    /**
     * Register package blade's directives
     */
    public function registerBladeDirective(): void;

    /**
     * Register package views dir
     */
    public function registerViews(): void;
}