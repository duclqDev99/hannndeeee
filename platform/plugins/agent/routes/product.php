<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Agent\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'agent-products', 'as' => 'agent.products.'], function () {
            Route::get('get-all-products-and-variations-in-agency', [
                'as' => 'get-all-products-and-variations-in-agency',
                'uses' => 'AgentProductController@getAllProductAndVariations',
                // 'permission' => 'products.index',
            ]);
            Route::get('get-all-product-parent', [
                'as' => 'get-all-product-parent',
                'uses' => 'AgentProductController@getAllProductParent',
                'permission' => false,
            ]);
            Route::get('get-product-in-agent/{id}', [
                'as' => 'get-product-in-agent',
                'uses' => 'AgentProductController@getProductInAgent',
                'permission' => false,
            ]);

        });
    });
});

