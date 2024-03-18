<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\QrScan\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'qr-scans', 'as' => 'qr-scan.'], function () {
            Route::resource('', 'QrScanController')->parameters(['' => 'qr-scan']);
        });
    });

});
