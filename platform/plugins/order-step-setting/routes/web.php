<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\OrderStepSetting\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        // Route::group(['prefix' => 'order-step', 'as' => 'order-step.'], function(){
        //     Route::match(['get', 'post'], '', [
        //         'as' => 'index',
        //         'uses' => 'OrderStepController@index',
        //         'permission' => false
        //     ]);
        //     Route::get('show-steps', [
        //         'as' => 'view-steps',
        //         'uses' => 'OrderStepController@showSteps',
        //         'permission' => false
        //     ]);
        //     Route::get('show-step-detail', [
        //         'as' => 'view-step-detail',
        //         'uses' => 'OrderStepController@showStepDetail',
        //         'permission' => false
        //     ]);
        // });
    });
});
