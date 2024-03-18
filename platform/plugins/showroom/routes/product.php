<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Showroom\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'showroom-products', 'as' => 'showroom.products.'], function () {
            Route::get('get-all-products-and-variations-in-agency', [
                'as' => 'get-all-products-and-variations-in-agency',
                'uses' => 'ShowroomProductController@getAllProductAndVariations',
                // 'permission' => addAllPermissionShowroom([])'products.index',
            ]);

            Route::get('check-product-order-by-showroom', [
                'as' => 'check-product-order-by-showroom',
                'uses' => 'ShowroomProductController@checkProductOrderInShowroom',
                'permission' => addAllPermissionShowroom(['showroom.orders.create']),
            ]);

            Route::get('get-product-and-variation-by-id', [
                'as' => 'get-product-and-variation-by-id',
                'uses' => 'ShowroomProductController@getProductAndVariationsById',
                'permission' => addAllPermissionShowroom(['showroom.products.get-product-and-variation-by-id']),
            ]);
            Route::get('get-product-in-showroom/{id}', [
                'as' => 'get-product-in-showroom',
                'uses' => 'ShowroomProductController@getProductInShowroom',
                'permission' => false,
            ]);
            Route::get('get-all-product-parent', [
                'as' => 'get-all-product-parent',
                'uses' => 'ShowroomProductController@getAllProductParent',
                'permission' => false,
            ]);

            Route::post('confirm-return-product', [
                'as' => 'confirm-return-product',
                'uses' => 'ShowroomProductController@postConfirmReturnProduct',
                'permission' => false,
            ]);
        });
    });
});

