<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Showroom\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'showroom-customers', 'as' => 'showroom.customers.'], function () {
            Route::post('store', [
                'as' => 'create.store',
                'uses' => 'ShowroomCustomerController@store',
                'permission' => addAllPermissionShowroom(['showroom.orders.create']),
            ])->wherePrimaryKey();
            Route::get('check-user-register-app', [
                'as' => 'check-user-register-app',
                'uses' => 'ShowroomCustomerController@checkUserRegisterApp',
                'permission' => addAllPermissionShowroom(['showroom.orders.create']),
            ])->wherePrimaryKey();
            Route::get('get-list-customers-for-search', [
                'as' => 'get-list-customers-for-search',
                'uses' => 'ShowroomCustomerController@getListCustomerForSearch',
                'permission' => addAllPermissionShowroom(['showroom.orders.create']),
            ]);
            Route::get('get-customer-order-numbers/{id}', [
                'as' => 'get-customer-order-numbers',
                'uses' => 'ShowroomCustomerController@getCustomerOrderNumbers',
                'permission' => addAllPermissionShowroom(['showroom.orders.create']),
            ]);
        });
    });
});

