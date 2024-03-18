<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\ProcedureOrder\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'procedure-orders', 'as' => 'procedure-order.'], function () {
            Route::resource('', 'ProcedureOrderController')->parameters(['' => 'procedure-order']);
        });

        Route::group(['prefix' => 'procedure-groups', 'as' => 'procedure-groups.'], function () {
            Route::resource('', 'ProcedureGroupController')->parameters(['' => 'procedure-group']);

            Route::get('update-status/{data}', [
                'uses' => 'ProcedureGroupController@updateStatus',
                // 'permission' => 'receipt.code',
            ])->name('update-status');

            Route::get('get-procedure-by-id/{data}', [
                'uses' => 'ProcedureGroupController@getProcedureById',
                // 'permission' => 'receipt.code',
            ])->name('get-procedure-by-id');

            Route::get('get-procedure-flowchart-by-id/{data}', [
                'uses' => 'ProcedureGroupController@getFlowchartById',
                // 'permission' => 'receipt.code',
            ])->name('get-procedure-flowchart-by-id');

            Route::get('order/create/{data}', [
                'uses' => 'ProcedureGroupController@orderCreate',
                // 'permission' => 'receipt.code',
            ])->name('order.create');

            Route::post('order/create/{data}', [
                'uses' => 'ProcedureGroupController@orderStore',
                // 'permission' => 'receipt.code',
            ])->name('order.store');


            Route::get('order/edit/{data}', [
                'uses' => 'ProcedureGroupController@orderEdit',
                // 'permission' => 'receipt.code',
            ])->name('order.edit');

            Route::post('order/edit/{data}', [
                'uses' => 'ProcedureGroupController@orderUpdate',
                // 'permission' => 'receipt.code',
            ])->name('order.update');

            Route::delete('order/delete/{data}', [
                'uses' => 'ProcedureGroupController@orderDelete',
                // 'permission' => 'receipt.code',
            ])->name('order.delete');
        });
    });

});
