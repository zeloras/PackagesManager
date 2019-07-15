<?php

return [
    'registration_name' => 'modules',
    'namespace' => 'Modules',
    'module_prefix' => 'module_',
    'admin_route_prefix' => 'admin.',
    'module_config' => 'modules.php',
    'paths' => [
        'modules_dir' => base_path('Modules'),
        'modules_storage' => storage_path('Modules'),
        'modules_storage_app' => storage_path('app/modules'),
        'assets' => public_path('modules'),
        'migration' => base_path('database/migrations'),
        'main_module_bundle' => 'module.json',
        'main_module_used' => 'modules.used',
        'main_module_composer' => 'composer.json',
        'repositories' => 'repo.json',
        'main_config_path' => 'src/Config/config.php',
        'module_config_path' => 'src/Config/modules.php',

        'modules' => 'modules',
        'resources' => 'resources',
        'main_lang' => 'src/lang/modules/',
        'main_view' => 'src/views/modules/',
        'module_routes' => 'src/Http/routes.php',
        'module_lang' => 'src/Resources/lang',
        'module_view' => 'src/Resources/views',
        'module_factories' => 'src/Database/factories',
        'module_migrations' => 'src/Database/Migrations',
        'rules_map' => 'src/Models/Validators/Rules.php'
    ],
    'scan' => [
        'enabled' => true,
        'paths' => [
            base_path('vendor/geekcms/*'),
        ],
    ],
    'cache' => [
        'enabled' => false,
        'key' => 'laravel-modules',
        'lifetime' => 60,
    ],
    'register' => [
        'translations' => true,
        /**
         * load files on boot or register method
         *
         * Note: boot not compatible with asgardcms
         *
         * @example boot|register
         */
        'files' => 'register',
    ],
    'repositories' => [
        'github' => [
            'link' => 'git@github.com:',
            'ext' => '.git'
        ],
        'github-https' => [
            'link' => 'https://github.com/:',
            'ext' => '.git'
        ],
        'gitlab' => [
            'link' => 'git@gitlab.com:',
            'ext' => '.git'
        ],
        'bitbucket' => [
            'link' => 'git@bitbucket.org:',
            'ext' => '.git'
        ],
        'default' => [
            'link' => '',
            'ext' => '.git'
        ]
    ],
    'default_branch' => 'master',
    'default_package_branch' => 'dev-master',
];
