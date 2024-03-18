<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' =>  ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'hgf', 'as' => 'hgf.'], function () {

            Route::group(['namespace' => 'Botble\OrderHgf\Http\Controllers\Admin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
                //Yêu cầu đặt hàng
                Route::group(['prefix' => 'purchase-order', 'as' => 'purchase-order.'], function () {
                    Route::resource('', 'PurchaseOrderController')->parameters(['' => 'order']);
                    Route::match(['get', 'post'], '', [
                        'as' => 'index',
                        'uses' => 'PurchaseOrderController@index',
                        'permission' => 'hgf.admin.purchase-order.index'
                    ]);

                    Route::get('show/{order}', [
                        'as' => 'show',
                        'uses' => 'PurchaseOrderController@show',
                        'permission' => 'hgf.admin.purchase-order.index'
                    ]);

                    Route::post('confirm', [
                        'as' => 'confirm',
                        'uses' => 'PurchaseOrderController@confirm',
                        'permission' => 'hgf.admin.purchase-order.index'
                    ]);
                });
                //Đơn đặt hàng
                Route::group(['prefix' => 'production', 'as' => 'production.'], function () {
                    Route::resource('', 'ProductionController')->parameters(['' => 'order']);
                    Route::match(['get', 'post'], '', [
                        'as' => 'index',
                        'uses' => 'ProductionController@index',
                        'permission' => 'hgf.admin.production.index'
                    ]);
                    Route::get('show/{production}', [
                        'as' => 'show',
                        'uses' => 'ProductionController@show',
                        'permission' => 'hgf.admin.production.index'
                    ]);
                });
            });
        });
    });
});
