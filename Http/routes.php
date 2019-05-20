<?php
Route::group(['middleware' => ['web', 'permission:admin_access'], 'prefix' => getAdminPrefix('packages')], function () {
    Route::get('/', 'Modules\Packages\Http\Controllers\AdminController@index')->name('admin.packages');
    Route::get('/list', 'Modules\Packages\Http\Controllers\AdminController@list')->name('admin.packages.list');
});
