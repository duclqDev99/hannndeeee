<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Sales\Http\Controllers\HandleStepController;
use Botble\Sales\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Sales\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::post('update-step', [HandleStepController::class, 'updateStep']);

        //Route quản lý thông tin khách hàng
        Route::group(['prefix' => 'customer-order', 'as' => 'customer-purchase.'], function () {
            Route::resource('', 'CustomerController')->parameters(['' => 'customer']);

            Route::post('create-customer-when-creating-order', [
                'as' => 'create-customer-when-creating-order',
                'uses' => 'CustomerController@postCreateCustomerWhenCreatingOrder',
                // 'permission' => ['customers.index', 'orders.index'],
            ]);
        });

        //Route quản lý thông tin đơn đặt hàng của khách hàng
        Route::group(['prefix' => 'customer/order', 'as' => 'purchase-order.'], function () {
            Route::get('prepare-id-for-action/{id}', fn () => redirect()->route('purchase-order.index'))->name('prepare-id-for-action');

            Route::resource('', 'OrderController')->parameters(['' => 'order']);
           
            Route::post('check-data-before-create-order', [
                'as' => 'check-data-before-create-order',
                'uses' => 'OrderController@checkDataBeforeCreateOrder',
                'permission' => 'purchase-order.create',
            ]);

            Route::post('reoder/update/{order}', [
                'as' => 'reoder.update',
                'uses' => 'OrderController@editOrderCustomer',
                'permission' => 'purchase-order.create',
            ]);

            //Reorder
            Route::get('reorder', [
                'as' => 'reorder',
                'uses' => 'OrderController@getReorder',
                'permission' => 'purchase-order.create',
            ]);

            //Tạo sản mới phẩm khi tạo đơn đặt hàng
            Route::post('create-product-when-creating-order', [
                'as' => 'create-product-when-creating-order',
                'uses' => 'OrderController@postCreateProductWhenCreatingOrder',
                'permission' => 'purchase-order.create',
            ]);

            Route::get('get-all-products-and-variations', [
                'as' => 'get-all-products-and-variations',
                'uses' => 'OrderController@getAllProductAndVariations',
                'permission' => false,
            ]);

            Route::get('generate-invoice/{order}', [
                'as' => 'generate-invoice',
                'uses' => 'OrderController@getGenerateInvoice',
                'permission' => 'purchase-order.edit',
            ])->wherePrimaryKey();
            
            ////////////////////////////////////////////////////////////////
            /**--------------- Xác nhận đơn hàng từ admin -------------- **/
            ////////////////////////////////////////////////////////////////
            Route::post('confirm/{order}', [
                'as' => 'confirm',
                'uses' => 'OrderController@postConfirm',
                'permission' => 'purchase-order.censorship',
            ]);

            Route::post('cancel/{order}', [
                'as' => 'cancel',
                'uses' => 'OrderController@cancelPurchaseOrder',
                'permission' => 'purchase-order.censorship',
            ]);

            Route::get('list-link-purchase-order', [
                'as' => 'list-link-purchase-order',
                'uses' => 'OrderController@getListPurchaseOrder',
            ]);
        });

        Route::group(['prefix' => 'quotation', 'as' => 'order-quotation.'], function(){
            Route::resource('', 'QuotationController')->parameters(['' => 'customer']);

            Route::get('/create/{order}', [
                'as' => 'create',
                'uses' => 'QuotationController@create',
                'permission' => 'order-quotation.*',
            ]);
            
            Route::post('/store', [
                'as' => 'create.store',
                'uses' => 'QuotationController@store',
                'permission' => 'order-quotation.create',
            ]);
            
            Route::post('/cancel/{id}', [
                'as' => 'cancel',
                'uses' => 'QuotationController@cancelAttach',
                'permission' => 'order-quotation.cencorship',
            ]);
        });

        //Route danh sách sản phẩm mẫu
        Route::group(['prefix' => 'product-sample', 'as' => 'product-sample.'], function(){
            Route::get('view/{id}', [
                'as' => 'view',
                'uses' => 'ProductSampleController@view',
                'permission' => false
            ]);
            Route::post('view/{product}', [
                'as' => 'update',
                'uses' => 'ProductSampleController@update',
                'permission' => false
            ]);
        });

        Route::group(['prefix' => 'order-production', 'as' => 'order-production.'], function(){
            Route::resource('', 'OrderProductionController')->parameters(['' => 'order-production']);

            Route::get('prepare-id-for-action/{id}', fn () => redirect()->route('purchase-order.index'))->name('prepare-id-for-action');

            Route::resource('', 'OrderProductionController')->parameters(['' => 'order-production']);

            Route::post('check-data-before-create-order', [
                'as' => 'check-data-before-create-order',
                'uses' => 'OrderProductionController@checkDataBeforeCreateOrder',
                'permission' => 'order-production.create',
            ]);

            Route::post('reoder/update/{order}', [
                'as' => 'reoder.update',
                'uses' => 'OrderProductionController@editOrderCustomer',
                'permission' => 'order-production.create',
            ]);

            //Reorder
            Route::get('reorder', [
                'as' => 'reorder',
                'uses' => 'OrderProductionController@getReorder',
                'permission' => 'order-production.create',
            ]);

            //Tạo sản mới phẩm khi tạo đơn đặt hàng
            Route::post('create-product-when-creating-order', [
                'as' => 'create-product-when-creating-order',
                'uses' => 'OrderProductionController@postCreateProductWhenCreatingOrder',
                'permission' => 'order-production.create',
            ]);

            Route::get('get-all-products-and-variations', [
                'as' => 'get-all-products-and-variations',
                'uses' => 'OrderProductionController@getAllProductAndVariations',
                'permission' => false,
            ]);

            Route::get('generate-invoice/{order}', [
                'as' => 'generate-invoice',
                'uses' => 'OrderProductionController@getGenerateInvoice',
                'permission' => 'order-production.edit',
            ])->wherePrimaryKey();
            
            ////////////////////////////////////////////////////////////////
            /**--------------- Xác nhận đơn hàng từ admin -------------- **/
            ////////////////////////////////////////////////////////////////
            Route::post('confirm/{order}', [
                'as' => 'confirm',
                'uses' => 'OrderProductionController@postConfirm',
                'permission' => 'order-production.censorship',
            ]);

            Route::post('cancel/{order}', [
                'as' => 'cancel',
                'uses' => 'OrderProductionController@cancelPurchaseOrder',
                'permission' => 'order-production.censorship',
            ]);

            Route::get('list-link-order-production', [
                'as' => 'list-link-order-production',
                'uses' => 'OrderProductionController@getListPurchaseOrder',
            ]);
        });

        Route::group(['prefix' => 'hgf', 'as' => 'hgf.'], function(){
            Route::group(['prefix' => 'admin', 'as' => 'admin.'], function(){
                Route::group(['prefix' => 'requesting-order', 'as' => 'requesting-order.'], function(){
                    Route::match(['get', 'post'] ,'', [
                        'as' => 'index',
                        'uses' => 'HgfAdminController@index',
                        'permission' => 'purchase-order.index'
                    ]);
                });
            });
        });
    });
});
