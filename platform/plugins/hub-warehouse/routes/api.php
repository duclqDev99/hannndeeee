<?php

use Illuminate\Support\Facades\Route;

//Custom
Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\HubWarehouse\Http\Controllers\API',
], function () {
    Route::group(['middleware' => ['core']], function () {
        Route::post('print-qrcode-for-batch-of-hub', 'ProposalHubReceiptApiController@printQRCodeForBatch');
        Route::get('created-shipment/{id}', 'ProposalHubReceiptApiController@getListCreatedShipment');
    });
    Route::group(['middleware' => ['api_key']], function () {
        Route::get('product-all-hub', 'HubWarehouseApiController@getAllProductHub');
        Route::get('get-list-hub', 'HubWarehouseApiController@getListHub');
        Route::get('product-of-hub/{id}', 'HubWarehouseApiController@getProductOfHub');
    });

});
