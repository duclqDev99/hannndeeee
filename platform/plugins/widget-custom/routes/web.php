<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\WidgetCustom\Http\Controllers'], function () {
    AdminHelper::registerRoutes(function () {
        Route::group(['prefix' => 'custom-widgets'], function () {
            Route::get('load-widget-custom', 'WidgetController@showWidget');

            Route::get('', [
                'as' => 'widgets-custom.index',
                'uses' => 'WidgetController@index',
            ]);

            Route::post('save-widgets-to-sidebar', [
                'as' => 'widgets-custom.save_widgets_sidebar',
                'uses' => 'WidgetController@update',
                'permission' => 'widgets-custom.index',
            ]);

            Route::delete('delete', [
                'as' => 'widgets-custom.destroy',
                'uses' => 'WidgetController@destroy',
                'permission' => 'widgets-custom.index',
            ]);
        });
    });
});
