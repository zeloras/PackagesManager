<?php


namespace GeekCms\PackagesManager\Support;


interface MainServiceInterface
{
    /**
     * Level for dirname
     */
    public const PARENT_LEVEL_DIR = 1;

    /**
     * Load core components
     *
     * @param string|null $main_name
     */
    public function loadCoreComponents(string $main_name = null): void;

    /**
     * Init main variables
     *
     * @param string|null $name
     * @param string|null $path
     */
    public function initVariables(string $name = null, string $path = null): void;

    /**
     * Register package menu item
     */
    public function registerNavigation(): void;

    /**
     * Register package models rules
     */
    public function registerValidationRules(): void;

    /**
     * Get cached services path
     *
     * @return string
     */
    public function getCachedServicesPath(): string;

    /**
     * Get package requirements
     *
     * @return array
     */
    public function getUnresolvedRequirements(): array;
}