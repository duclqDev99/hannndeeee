<?php
use Illuminate\Support\Facades\Route;
use Botble\Base\Facades\BaseHelper;

Route::group(['namespace' => 'Botble\NotiAdminPusher\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::get('get-all-permission-by-user',[
            'as' => 'get-all-permission-by-user',
            'uses' => 'NotiAdminPusherController@getAllPermissionByUser',
            'permission' => false,
        ]);
    });

    Route::group(['prefix' => 'notifications', 'as' => 'notifications.', 'permission' => false], function () {
        Route::get('/', [
            'as' => 'index',
            'uses' => 'NotificationController@index',
        ]);

        Route::delete('{id}', [
            'as' => 'destroy',
            'uses' => 'NotificationController@destroy',
        ])->wherePrimaryKey();

        Route::get('read-notification/{id}', [
            'as' => 'read-notification',
            'uses' => 'NotificationController@read',
        ])->wherePrimaryKey();

        Route::put('read-all-notification', [
            'as' => 'read-all-notification',
            'uses' => 'NotificationController@readAll',
        ]);

        Route::delete('destroy-all-notification', [
            'as' => 'destroy-all-notification',
            'uses' => 'NotificationController@deleteAll',
        ]);

        Route::get('count-unread', [
            'as' => 'count-unread',
            'uses' => 'NotificationController@countUnread',
        ]);
    });

});