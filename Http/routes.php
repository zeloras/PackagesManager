<?php

Route::group(['middleware' => ['web', 'permission:admin_access'], 'prefix' => getAdminPrefix('packages')], function () {
    Route::get('/', 'GeekCms\PackagesManager\Http\Controllers\AdminController@index')->name('admin.packages');
    Route::get('/list', 'GeekCms\PackagesManager\Http\Controllers\AdminController@list')->name('admin.packages.list');
});
