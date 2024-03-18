<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' =>  ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'retail', 'as' => 'retail.'], function () {

            Route::group(['namespace' => 'Botble\OrderRetail\Http\Controllers\Sale', 'prefix' => 'sale', 'as' => 'sale.'], function () {
                //Yêu cầu đặt hàng
                Route::group(['prefix' => 'purchase-order', 'as' => 'purchase-order.'], function () {
                    // Route::resource('', 'PurchaseOrderController')->parameters(['' => 'order']);
                    
                    Route::match(['get', 'post'], '', [
                        'as' => 'index',
                        'uses' => 'PurchaseOrderController@index',
                        'permission' => 'retail.sale.purchase-order.index'
                    ]);

                    Route::get('show/{order}', [
                        'as' => 'show',
                        'uses' => 'PurchaseOrderController@show',
                        'permission' => false
                    ]);

                    Route::post('search', [
                        'as' => 'search',
                        'uses' => 'PurchaseOrderController@search',
                        'permission' => false
                    ]);

                    Route::post('append-to-quotation-form', [
                        'as' => 'append-to-quotation-form',
                        'uses' => 'PurchaseOrderController@appendToQuotationForm',
                        'permission' => false
                    ]);

                    Route::post('append-to-production-form', [
                        'as' => 'append-to-production-form',
                        'uses' => 'PurchaseOrderController@appendToProductionForm',
                        'permission' => false
                    ]);

                    Route::get('create', [
                        'as' => 'create',
                        'uses' => 'PurchaseOrderController@create',
                        'permission' => 'retail.sale.purchase-order.create'
                    ]);

                    Route::post('store', [
                        'as' => 'create.store',
                        'uses' => 'PurchaseOrderController@store',
                        'permission' => 'retail.sale.purchase-order.create'
                    ]);

                    Route::get('edit/{order}', [
                        'as' => 'edit',
                        'uses' => 'PurchaseOrderController@edit',
                        'permission' => 'retail.sale.purchase-order.edit'
                    ]);

                    Route::post('update/{order}', [
                        'as' => 'update',
                        'uses' => 'PurchaseOrderController@update',
                        'permission' => 'retail.sale.purchase-order.edit'
                    ]);

                    Route::post('delete/{order}', [
                        'as' => 'delete',
                        'uses' => 'PurchaseOrderController@destroy',
                        'permission' => 'retail.sale.purchase-order.destroy'
                    ]);

                    Route::get('get-add-product-form', [
                        'as' => 'get-add-product-form',
                        'uses' => 'PurchaseOrderController@addProductToPurchaseOrderForm',
                        'permission' => 'retail.sale.purchase-order.create'
                    ]);
                });

                Route::group(['prefix' => 'quotation', 'as' => 'quotation.'], function () {
                    Route::resource('', 'QuotationController')->parameters(['' => 'quotation']);

                    Route::match(['get', 'post'], '', [
                        'as' => 'index',
                        'uses' => 'QuotationController@index',
                        'permission' => 'retail.sale.quotation.index'
                    ]);

                    Route::get('show/{quotation}', [
                        'as' => 'show',
                        'uses' => 'QuotationController@show',
                        'permission' => 'retail.sale.quotation.index'
                    ]);

                    Route::get('create', [
                        'as' => 'create',
                        'uses' => 'QuotationController@create',
                        'permission' => 'retail.sale.quotation.create'
                    ]);

                    Route::post('store', [
                        'as' => 'create.store',
                        'uses' => 'QuotationController@store',
                        'permission' => 'retail.sale.quotation.create'
                    ]);

                    Route::get('edit/{quotation}', [
                        'as' => 'edit',
                        'uses' => 'QuotationController@edit',
                        'permission' => 'retail.sale.quotation.edit'
                    ]);

                    Route::post('update/{quotation}', [
                        'as' => 'update',
                        'uses' => 'QuotationController@update',
                        'permission' => 'retail.sale.quotation.edit'
                    ]);

                    Route::post('delete/{order}', [
                        'as' => 'delete',
                        'uses' => 'QuotationController@destroy',
                        'permission' => 'retail.sale.quotation.destroy'
                    ]);

                    Route::post('upload-contract', [
                        'as' => 'upload-contract',
                        'uses' => 'QuotationController@uploadContract',
                        'permission' => 'retail.sale.quotation.sign_contact'
                    ]);

                    Route::get('get-invoice/{quotation}', [
                        'as' => 'get-invoice',
                        'uses' => 'QuotationController@getQuotationInvoice',
                        'permission' => 'retail.sale.quotation.index'
                    ]);
                });

                Route::group(['prefix' => 'production', 'as' => 'production.'], function () {
                    Route::resource('', 'ProductionController')->parameters(['' => 'quotation']);

                    Route::match(['get', 'post'], '', [
                        'as' => 'index',
                        'uses' => 'ProductionController@index',
                        'permission' => 'retail.sale.production.index'
                    ]);

                    Route::get('show/{production}', [
                        'as' => 'show',
                        'uses' => 'ProductionController@show',
                        'permission' => 'retail.sale.production.index'
                    ]);

                    Route::get('create', [
                        'as' => 'create',
                        'uses' => 'ProductionController@create',
                        'permission' => 'retail.sale.production.create'
                    ]);

                    Route::post('store', [
                        'as' => 'create.store',
                        'uses' => 'ProductionController@store',
                        'permission' => 'retail.sale.production.create'
                    ]);   

                    Route::get('edit/{production}', [
                        'as' => 'edit',
                        'uses' => 'ProductionController@edit',
                        'permission' => 'retail.sale.production.edit'
                    ]);

                    Route::post('update/{production}', [
                        'as' => 'update',
                        'uses' => 'ProductionController@update',
                        'permission' => 'retail.sale.production.edit'
                    ]);
                });

                Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
                    Route::get('edit/{product}', [
                        'as' => 'edit',
                        'uses' => 'ProductController@edit',
                        'permission' => 'retail.sale.purchase-order.edit'
                    ]);

                    Route::post('get-by-order', [
                        'as' => 'show',
                        'uses' => 'ProductController@getByOrder',
                        'permission' => 'retail.sale.purchase-order.index'
                    ]);

                    Route::post('update', [
                        'as' => 'update',
                        'uses' => 'ProductController@update',
                        'permission' => 'retail.sale.purchase-order.edit'
                    ]);
                });
            });

            Route::group(['namespace' => 'Botble\OrderRetail\Http\Controllers\Accountant', 'prefix' => 'accountant', 'as' => 'accountant.'], function () {
                // Báo giá
                Route::group(['prefix' => 'quotation', 'as' => 'quotation.'], function () {

                    Route::match(['get', 'post'], '', [
                        'as' => 'index',
                        'uses' => 'QuotationController@index',
                        'permission' => 'retail.accountant.quotation.index'
                    ]);   

                    Route::get('show/{quotation}', [
                        'as' => 'show',
                        'uses' => 'QuotationController@show',
                        'permission' => 'retail.accountant.quotation.index'
                    ]);
                });
            });
        });
    });
});
