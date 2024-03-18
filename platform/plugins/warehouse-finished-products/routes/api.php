<?php

use Illuminate\Support\Facades\Route;

//Custom


Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\WarehouseFinishedProducts\Http\Controllers\API',
], function () {
    Route::group(['middleware' => ['core','api_key.client']], function () {
        Route::get('get-products-in-stock', 'ProposalGoodsApiController@getProductsInStock');
        Route::get('get-list-processing-house', 'ProposalGoodsApiController@getAllProcessingHourse');
        Route::get('get-list-products', 'ProposalGoodsApiController@getAllProducts');
        Route::get('get-info-proposal-receipt-product/{id}', 'ProposalGoodsApiController@getInfoProposalReceiptProductById');
        Route::post('validate-proposal-receipt', 'ProposalGoodsApiController@checkValidatePurchaseGoods');
        Route::post('print-qrcode-for-batch', 'ProposalGoodsApiController@printQRCodeForBatch');
        Route::get('get-created-shipment/{id}', 'ProposalGoodsApiController@getListCreatedShipment');
        Route::get('get-batch-in-warehouse', 'ProposalGoodsApiController@getParentProductInWarehouse');
        Route::get('get-list-product-parent', 'ProposalGoodsApiController@getAllProductParent');
    });
});
