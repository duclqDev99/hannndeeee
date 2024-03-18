<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\OverviewReport\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'overview-reports', 'as' => 'overview-report.'], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'OverviewReportController@index',
                'permission' => 'overview-report.index',
            ]);

            Route::post('get-data-report-of-agent', [
                'as' => 'get-data-report-of-agent',
                'uses' => 'OverviewReportController@getDataReportOfAgent',
                'permission' => 'overview-report.index',

            ]);

            Route::post('get-data-report-of-showroom', [
                'as' => 'get-data-report-of-showroom',
                'uses' => 'OverviewReportController@getDataReportOfShowroom',
                'permission' => 'overview-report.index',
            ]);

            Route::post('get-data-report-of-hub', [
                'as' => 'get-data-report-of-hub',
                'uses' => 'OverviewReportController@getDataReportOfHub',
                'permission' => 'overview-report.index',
            ]);

            Route::post('top-selling-products', [
                'as' => 'top-selling-products',
                'uses' => 'OverviewReportController@getTopSellingProducts',
                'permission' => 'overview-report.index',
            ]);
            Route::post('recent-orders', [
                'as' => 'recent-orders',
                'uses' => 'OverviewReportController@getRecentOrders',
                'permission' => 'overview-report.index',
            ]);

        });
    });

});
