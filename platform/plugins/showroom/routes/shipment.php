<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group(['namespace' => 'Botble\Showroom\Http\Controllers'], function () {
        Route::group(['prefix' => 'showroom-shipments', 'as' => 'showroom.shipments.'], function () {
            Route::resource('', 'ShowroomShipmentController')
                ->parameters(['' => 'shipment'])
                ->except(['create', 'store']);

            Route::post('print',[
                'as' => 'print',
                'uses' => 'ShowroomShipmentController@print',
                'permissions' => 'showroom.orders.edit'
            ]);
            
            Route::post('update-status/{shipment}', [
                'as' => 'update-status',
                'uses' => 'ShowroomShipmentController@postUpdateStatus',
                'permission' => 'showroom.orders.edit',
            ])->wherePrimaryKey();

            Route::post('update-cod-status/{shipment}', [
                'as' => 'update-cod-status',
                'uses' => 'ShowroomShipmentController@postUpdateCodStatus',
                'permission' => 'showroom.orders.edit',
            ])->wherePrimaryKey();
        });
    });
});
