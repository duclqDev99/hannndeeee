<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\CustomerBookOrder\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'customer-book-orders', 'as' => 'customer-book-order.'], function () {
            Route::resource('', 'CustomerBookOrderController')->parameters(['' => 'customer-book-order']);
        });
    });

});

Route::group(['namespace' => 'Botble\CustomerBookOrder\Http\Controllers', 'middleware' => ['web']], function () {
    Route::group(['prefix' => 'customer-book-orders', 'as' => 'customer-book-order.'], function () {
        Route::post('create-customer-book-order', [
            'as' => 'create.front',
            'uses' => 'CustomerBookOrderController@storeFront',
            'permission' => false
        ]);
    });
});