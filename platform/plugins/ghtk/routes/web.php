<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\GHTK\Http\Controllers'], function () {
    AdminHelper::registerRoutes(function () {
        Route::group([
            'prefix' => 'shipments/ghtk',
            'as' => 'ecommerce.shipments.ghtk.',
            'permission' => 'ecommerce.shipments.index',
        ], function () {
            Route::controller('GHTKController')->group(function () {
                Route::get('show/{id}', [
                    'as' => 'show',
                    'uses' => 'show',
                ]);

                Route::post('transactions/create/{id}', [
                    'as' => 'transactions.create',
                    'uses' => 'createTransaction',
                    'permission' => 'ecommerce.shipments.edit',
                ]);

                Route::get('rates/', [
                    'as' => 'rates',
                    'uses' => 'getRates',
                ]);

                Route::post('update-rate/{id}', [
                    'as' => 'update-rate',
                    'uses' => 'updateRate',
                    'permission' => 'ecommerce.shipments.edit',
                ]);

                Route::get('view-logs/{file}', [
                    'as' => 'view-log',
                    'uses' => 'viewLog',
                ]);
            });

            Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
                Route::post('update', [
                    'as' => 'update',
                    'uses' => 'GHTKSettingController@update',
                    'middleware' => 'preventDemo',
                    'permission' => 'shipping_methods.index',
                ]);
            });
        });
    });

    if (is_plugin_active('marketplace')) {
        Theme::registerRoutes(function () {
            Route::group([
                'prefix' => 'vendor',
                'as' => 'marketplace.vendor.',
                'middleware' => ['vendor'],
            ], function () {
                Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
                    Route::group(['prefix' => 'ghtk', 'as' => 'ghtk.'], function () {
                        Route::controller('GHTKController')->group(function () {
                            Route::get('show/{id}', [
                                'as' => 'show',
                                'uses' => 'show',
                            ]);

                            Route::post('transactions/create/{id}', [
                                'as' => 'transactions.create',
                                'uses' => 'createTransaction',
                            ]);

                            Route::get('rates/{id}', [
                                'as' => 'rates',
                                'uses' => 'getRates',
                            ]);

                            Route::post('update-rate/{id}', [
                                'as' => 'update-rate',
                                'uses' => 'updateRate',
                            ]);
                        });
                    });
                });
            });
        });
    }
});

Route::group([
    'namespace' => 'Botble\GHTK\Http\Controllers',
    'prefix' => 'ghtk/webhook',
    'middleware' => ['api', 'ghtk.webhook'],
    'as' => 'ghtk.webhook.',
], function () {
    Route::controller('GHTKWebhookController')->group(function () {
        Route::post('update-shipment', [
            'uses' => 'updateShipment',
            'as' => 'update-shipment',
        ]);
    });
});
