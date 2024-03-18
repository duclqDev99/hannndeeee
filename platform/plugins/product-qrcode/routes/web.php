<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\ProductQrcode\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'product-qrcodes', 'as' => 'product-qrcode.'], function () {
            Route::resource('', 'ProductQrcodeController')->parameters(['' => 'product-qrcode']);

            Route::get(
                'detail/{id}',
                'QrcodeDetailController@detail'
            )->name('detail');

            Route::post(
                'detail/{id}',
                'QrcodeDetailController@detail'
            )->name('detail');

            Route::get(
                'get-product-by-id/{id}',
                'ProductQrcodeController@getProductById'
            )->name('get-product-by-id');

            Route::post('update-status-detail', [
                'as' => 'update-status-detail',
                'uses' => 'QrcodeDetailController@updateStatusQRCodeDetail',
                'permission' => 'product-qrcode.edit',
            ]);

            Route::post('create-qrcodes', [
                'as' => 'create-qrcodes',
                'uses' => 'ProductQrcodeController@createQrCode',
                'permission' => 'product-qrcode.create',
            ]);

            Route::get('export-qrcodes', [
                'as' => 'export-qrcodes',
                'uses' => 'ProductQrcodeController@exportQrCode',
                'permission' => 'product-qrcode.export-qrcodes',
            ]);

            Route::get('get-qrcodes-by-id', [
                'as' => 'get-qrcode-by-id',
                'uses' => 'ProductQrcodeController@getQrCodeById',
                'permission' => 'product-qrcode.get-qrcode-by-id',
            ]);

            Route::get('print-times-count', [
                'as' => 'print-times-count',
                'uses' => 'ProductQrcodeController@printTimesCount',
                'permission' => 'product-qrcode.export-temporary',
            ]);

            Route::get('export-temporary', [
                'as' => 'export-temporary',
                'uses' => 'ProductQrcodeController@exportQrCodeTemporary',
                'permission' => 'product-qrcode.export-temporary',
            ]);

            Route::post(
                'ajax-post-qr-scan',
                [
                    'uses' => 'ProductQrcodeController@ajaxPostQrScan',
                    'permission' => false,
                ]
            )->name('ajax-post-product-qr-scan');

            Route::get(
                'print/{id}',
                'ProductQrcodeController@printQRcode'
            )->name('print');

            Route::get('get-all-products-and-variations', [
                'as' => 'get-all-products-and-variations',
                'uses' => 'ProductQrcodeController@getAllProductAndVariations',
                'permission' => 'products.index',
            ]);

        });
    });

});
