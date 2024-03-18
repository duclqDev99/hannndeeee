<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Showroom\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'showroom'], function () {

            Route::group(['prefix' => 'reports'], function () {
                Route::get('', [
                    'as' => 'showroom.report.index',
                    'uses' => 'ShowroomReportController@getIndex',
                ]);

                Route::post('top-selling-products', [
                    'as' => 'showroom.report.top-selling-products',
                    'uses' => 'ShowroomReportController@getTopSellingProducts',
                    'permission' => addAllPermissionShowroom(['showroom.report.index']),
                ]);

                Route::get('recent-orders', [
                    'as' => 'showroom.report.recent-orders',
                    'uses' => 'ShowroomReportController@getRecentOrders',
                    'permission' => addAllPermissionShowroom(['showroom.report.index']),
                ]);
                Route::post('recent-orders', [
                    'as' => 'showroom.report.recent-orders',
                    'uses' => 'ShowroomReportController@getRecentOrders',
                    'permission' => addAllPermissionShowroom(['showroom.report.index']),
                ]);

                Route::post('trending-products', [
                    'as' => 'showroom.report.trending-products',
                    'uses' => 'ShowroomReportController@getTrendingProducts',
                    'permission' => addAllPermissionShowroom(['showroom.report.index']),
                ]);
            });
        });
    });
});
