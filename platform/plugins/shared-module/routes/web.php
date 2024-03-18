<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\SharedModule\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::get('/telegram-chatid-setting',[
        'uses'=> 'TelegramSettingController@editChatId',
        'as' => 'telegram.setting.edit',
        'permissions' => 'telegram.setting',
    ]);
    Route::put('/telegram-chatid-setting',[
        'uses'=> 'TelegramSettingController@updateChatId',
        'as' => 'telegram.setting.update',
        'permissions' => 'telegram.setting',
    ]);
    Route::get('/get-districts-by-state',
    [
        'uses'=> 'SharedModuleController@ajaxGetDistrictsVietelPost',
        'as' => 'get-districts-by-state',
    ]);
    Route::get('/get-wards-by-district',
    [
        'uses'=> 'SharedModuleController@ajaxGetWardsVietelPost',
        'as' => 'get-wards-by-district',
    ]);
    Route::get('/get-provinces-vn',
    [
        'uses'=> 'SharedModuleController@ajaxGetProvincesVietelPost',
        'as' => 'get-provinces-vn',
    ]);
    Route::get('/get-address-all-showroom-viettelpost',
    [
        'uses'=> 'SharedModuleController@ajaxGetAddressAllShowroomViettelPost',
        'as' => 'get-address-all-showroom-viettelpost',
    ]);

});
