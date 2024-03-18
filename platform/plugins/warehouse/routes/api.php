<?php

use Illuminate\Support\Facades\Route;

//Custom


Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Warehouse\Http\Controllers\API',
], function () {
    Route::group(['middleware' => ['core', 'api_key.client']], function () {
        Route::get('warehouse/material', 'WarehouseApiController@getAllWarehouseMaterial');
        Route::get('stock/material/list', 'WarehouseApiController@getAllMaterialInStock');

        Route::get('material/list', 'MaterialApiController@getAllMaterial');
        Route::get('supplier/list', 'MaterialApiController@getAllSupplier');
        Route::post('validate/receipt', 'MaterialApiController@checkValidateReceipt');
        Route::post('validate/purchase-goods', 'MaterialApiController@checkValidatePurchaseGoods');
        Route::get('proposal/info/{id}', 'MaterialApiController@getInfoProposalById');
        Route::get('proposal-goods/info/{id}', 'MaterialApiController@getInfoProposalGoodsById');


        Route::get('getAllProcessingHouse','ProcessHouseApiController@getAllProcessHouse');
        Route::get('getAllWarehouse','WarehouseMaterialApiController@getAllWarehouse');
        Route::get('getWarehouseAll','WarehouseMaterialApiController@getWarehouseAll');

        Route::get('getListMaterialProposalOut/{id}','WarehouseMaterialApiController@getListMaterialProposalOut');
        Route::get('get-price-material/{id?}/{warehose_id?}', [
            'uses' => 'WarehouseMaterialApiController@getPriceMaterial',

        ]);
    });
});
