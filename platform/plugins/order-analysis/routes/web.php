<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\OrderAnalysis\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'analyses', 'as' => 'analyses.'], function () {
            Route::resource('', 'OrderAnalysisController')->parameters(['' => 'order-analysis']);

            Route::get('/approve/{analysis}', [
                'as' => 'approve',
                'uses' => 'OrderAnalysisController@getOrderAnalysisApprove'
            ]);

            // Route::get('order', [
            //     'as' => 'order.index',
            //     'uses' => 'OrderAnalysisController@orderIndex',
            //     'permission' => 'order-analysis.index',
            // ]);
        });
    });

});
