<?php

use Illuminate\Support\Facades\Route;

//Custom


Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Sales\Http\Controllers\API',
], function () {
    Route::group(['middleware' => ['core']], function () {
        Route::get('get-list-customers', 'PurchaseOrderAPIController@getListCustomers')->name('sale.get-list-customers');
        Route::post('get-information-product-attach', 'OrderAttachAPIController@getInformationProductAttach');
    });
});
