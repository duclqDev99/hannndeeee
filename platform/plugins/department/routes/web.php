<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Department\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'departments', 'as' => 'department.'], function () {
            Route::resource('', 'DepartmentController')->parameters(['' => 'department']);
        });

        Route::group(['prefix' => 'departments-user', 'as' => 'department-user.'], function () {
            Route::resource('', 'DepartmentUserController')->parameters(['' => 'department-user']);
        });
    });

});
