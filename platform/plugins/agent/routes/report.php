<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Agent\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'agent'], function () {

            Route::group(['prefix' => 'reports'], function () {
                Route::get('', [
                    'as' => 'agent.report.index',
                    'uses' => 'AgentReportController@getIndex',
                ]);

                Route::post('top-selling-products', [
                    'as' => 'agent.report.top-selling-products',
                    'uses' => 'AgentReportController@getTopSellingProducts',
                    'permission' => addAllPermissionAgent(['agent.report.index']),
                ]);

                Route::get('recent-orders', [
                    'as' => 'agent.report.recent-orders',
                    'uses' => 'AgentReportController@getRecentOrders',
                    'permission' => addAllPermissionAgent(['agent.report.index']),
                ]);
                Route::post('recent-orders', [
                    'as' => 'agent.report.recent-orders',
                    'uses' => 'AgentReportController@getRecentOrders',
                    'permission' => addAllPermissionAgent(['agent.report.index']),
                ]);

                Route::post('trending-products', [
                    'as' => 'agent.report.trending-products',
                    'uses' => 'AgentReportController@getTrendingProducts',
                    'permission' => addAllPermissionAgent(['agent.report.index']),
                ]);

                Route::post('export-report', [
                    'as' => 'agent.report.export-report',
                    'uses' => 'AgentReportController@exportReport',
                ]);
            });
        });
    });
});
