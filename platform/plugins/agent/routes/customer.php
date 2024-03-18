<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Agent\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'agent-customers', 'as' => 'agent.customers.'], function () {
            Route::post('store', [
                'as' => 'create.store',
                'uses' => 'AgentCustomerController@store',
                'permission' => addAllPermissionAgent(['agent.orders.create']),
            ])->wherePrimaryKey();
            Route::get('check-user-register-app', [
                'as' => 'check-user-register-app',
                'uses' => 'AgentCustomerController@checkUserRegisterApp',
                'permission' => addAllPermissionAgent(['agent.orders.create']),
            ])->wherePrimaryKey();
            Route::get('get-list-customers-for-search', [
                'as' => 'get-list-customers-for-search',
                'uses' => 'AgentCustomerController@getListCustomerForSearch',
                'permission' => addAllPermissionAgent(['agent.orders.create']),
            ]);
            Route::get('get-customer-order-numbers/{id}', [
                'as' => 'get-customer-order-numbers',
                'uses' => 'AgentCustomerController@getCustomerOrderNumbers',
                'permission' => addAllPermissionAgent(['agent.orders.create']),
            ]);
        });
    });
});

