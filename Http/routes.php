<?php

Route::group(['middleware' => ['web', 'permission:' . \Gcms::MAIN_ADMIN_PERMISSION], 'prefix' => getAdminPrefix('packages')], function () {
    Route::group(['middleware' => ['permission:modules_packagesmanager_admin_list']], function () {
        Route::get(DIRECTORY_SEPARATOR, 'GeekCms\PackagesManager\Http\Controllers\AdminController@index')->name('admin.packages');
        Route::get('/change-active/{module}', 'GeekCms\PackagesManager\Http\Controllers\AdminController@changeActive')->name('admin.packages.change_active');
        Route::get('/change-install/{module}', 'GeekCms\PackagesManager\Http\Controllers\AdminController@changeInstall')->name('admin.packages.change_install');
        Route::get('/list', 'GeekCms\PackagesManager\Http\Controllers\AdminController@list')->name('admin.packages.list');
    });
});
