<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\OrderRetail\Http\Controllers\Sale', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'order-step', 'as' => 'order-step.'], function () {
          
            Route::match(['get', 'post'], '', [
                'as' => 'index',
                'uses' => 'OrderStepController@index',
                'permission' => 'retail.view-progress'
            ]);
            Route::get('steps', [
                'as' => 'list',
                'uses'=> 'OrderStepController@viewSteps',
                'permission' => 'retail.view-progress'
            ]);
            Route::get('detail', [
                'as' => 'detail',
                'uses' => 'OrderStepController@viewStepDetail',
                'permission' => 'retail.view-progress'
            ]);
        });
    });
});
