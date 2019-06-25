<?php

Route::group(['middleware' => ['web', 'permission:admin_access'], 'prefix' => getAdminPrefix('packages')], function () {
    Route::group(['middleware' => ['permission:modules_packagesmanager_admin_list']], function () {
        Route::get('/', 'GeekCms\PackagesManager\Http\Controllers\AdminController@index')->name('admin.packages');
        Route::get('/list', 'GeekCms\PackagesManager\Http\Controllers\AdminController@list')->name('admin.packages.list');
    });
});
