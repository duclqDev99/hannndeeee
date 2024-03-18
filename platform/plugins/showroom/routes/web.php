<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Showroom\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'showrooms', 'as' => 'showroom.'], function () {
            Route::resource('', 'ShowroomController')->parameters(['' => 'showroom']);

            Route::get('get-all-showroom', [
                'as' => 'get-all-showroom',
                'uses' => 'ShowroomController@getAllShowroom',
                'permission' => false
            ]);

            Route::get('get-list-showroom-for-user', [
                'as' => 'get-list-showroom-for-user',
                'uses' => 'ShowroomController@getListShowroomForUser',
                'permission' => false

            ]);
            Route::get('get-list_product-showroom-for-user', [
                'as' => 'get-list_product-showroom-for-user',
                'uses' => 'ShowroomController@getListProductShowroomForUser',
                'permission' => false

            ]);
        });


        Route::group(['prefix' => 'showroom-warehouse', 'as' => 'showroom-warehouse.'], function () {
            Route::resource('', 'ShowroomWarehouseController')->parameters(['' => 'showroom-warehouse']);

            Route::get('create-product-manual/{id}', [
                'as' => 'create-product-manual',
                'uses' => 'ShowroomWarehouseController@createManual',
            ]);
            Route::post('store-product-manual', [
                'as' => 'store-product-manual',
                'uses' => 'ShowroomWarehouseController@storeManual',
            ]);
            Route::get('get-warehouse-by-showroom/{id}', [
                'as' => 'get-warehouse-by-showroom',
                'uses' => 'ShowroomWarehouseController@getWarehouseByShowroom',
                'permission' => false

            ]);
            Route::get('get-all-warehouse-showroom', [
                'as' => 'get-all-warehouse-showroom',
                'uses' => 'ShowroomWarehouseController@getAllWarehouseShowroom',
                'permission' => false

            ]);

            //Xem chi tiết lô hàng có trong kho
            Route::get('/detail-batch/{stock}', [
                'as' => 'detail-batch',
                'uses' => 'ShowroomWarehouseController@detailBatchInStock',
                'permission' => addAllPermissionShowroom(['showroom-warehouse.*']),
            ]);
            Route::post('/detail-batch/{stock}', [
                'uses' => 'ShowroomWarehouseController@detailBatchInStock',
                'permission' => addAllPermissionShowroom(['showroom-warehouse.*']),
            ]);
            Route::get('/detail-product/{stock}', [
                'as' => 'detail-product',
                'uses' => 'ShowroomWarehouseController@detailProductInStock',
                'permission' => addAllPermissionShowroom(['showroom-warehouse.*']),
            ]);
            Route::post('/detail-product/{stock}', [
                'uses' => 'ShowroomWarehouseController@detailProductInStock',
                'permission' => addAllPermissionShowroom(['showroom-warehouse.*']),
            ]);

            //Xem chi tiết san phảm hàng có trong kho
            Route::get('/detail-odd/{stock}', [
                'as' => 'detail-odd',
                'uses' => 'ShowroomWarehouseController@detailOddInStock',
                'permission' => addAllPermissionShowroom(['showroom-warehouse.detail-odd']),
            ]);
            Route::post('/detail-odd/{stock}', [
                'uses' => 'ShowroomWarehouseController@detailOddInStock',
                'permission' => addAllPermissionShowroom(['showroom-warehouse.detail-odd']),
            ]);
            Route::get('/reduce-quantity', [
                'as' => 'reduce-quantity',
                'uses' => 'ShowroomWarehouseController@reduceQuantity',
                'permission' => addAllPermissionShowroom(['showroom-warehouse.reduce-quantity']),
            ]);
            Route::post('/reduce-quantity', [
                'uses' => 'ShowroomWarehouseController@reduceQuantity',
            ]);
        });
        //        Route::group(['prefix' => 'showroom-product', 'as' => 'showroom-product.'], function () {
        //            Route::resource('', 'ShowroomProductController')->parameters(['' => 'showroom-product']);
        //        });
        Route::get('showroom/get-list-showroom-for-user', [
            'as' => 'showroom.get-list-showroom-for-user',
            'uses' => 'ShowroomController@getListShowroomForUser',
            'permission' => addAllPermissionShowroom(['showroom.orders.create']),
        ]);
        // Route::group(['prefix' => 'proposal-showroom-receipts', 'as' => 'proposal-showroom-receipt.'], function () {
        //     Route::resource('', 'ProposalShowroomReceiptController')->parameters(['' => 'proposal-showroom-receipt']);
        // });
        // Route::group(['prefix' => 'showroom-receipts', 'as' => 'showroom-receipt.'], function () {
        //     Route::resource('', 'ShowroomReceiptController')->parameters(['' => 'showroom-receipt']);
        // });

        Route::group(['prefix' => 'proposal-showroom-receipts', 'as' => 'proposal-showroom-receipt.'], function () {
            Route::resource('', 'ProposalShowroomReceiptController')->parameters(['' => 'proposal-showroom-receipt']);
            Route::get('proposal/{id}', [
                'as' => 'proposal',
                'uses' => 'ProposalShowroomReceiptController@proposal',
                'permission' => false
            ]);
            Route::get('get-batch/{id}', [
                'as' => 'get-batch',
                'uses' => 'ProposalShowroomReceiptController@getBatch',
                'permission' => false
            ]);
            Route::get('get-product-in-hub/{id}', [
                'as' => 'get-product-in-hub',
                'uses' => 'ProposalShowroomReceiptController@getProductInHub',
                'permission' => false
            ]);
            // Route::get('approve/{proposal}', [
            //     'as' => 'approve',
            //     'uses' => 'ProposalShowroomReceiptController@approveView',
            //     'permission' => false
            // ]);
            // Route::post('approve/{proposal}', [
            //     'as' => 'postApprove',
            //     'uses' => 'ProposalShowroomReceiptController@approve',
            //     'permission' => addAllPermissionShowroom(['proposal-showroom-receipt.approve'])
            // ]);
            Route::get('view/{proposal}', [
                'as' => 'view',
                'uses' => 'ProposalShowroomReceiptController@view',
                'permission' => addAllPermissionShowroom(['proposal-showroom-receipt.*'])
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'ProposalShowroomReceiptController@denied',
                'permission' => addAllPermissionShowroom(['proposal-showroom-receipt.approve'])
            ]);
            Route::get('get-warehouse-in-showroom', [
                'as' => 'get-warehouse-in-showroom',
                'uses' => 'ProposalShowroomReceiptController@getWarehouse',
                'permission' => false
            ]);
            Route::post('/export-file', [
                'uses' => 'ProposalShowroomReceiptController@getGenerateReceiptProduct',
                'permission' => addAllPermissionShowroom(['proposal-showroom-receipt.*']),
                'as' => 'export-file',
            ]);
        });

        Route::group(['prefix' => 'showroom-receipts', 'as' => 'showroom-receipt.'], function () {
            Route::resource('', 'ShowroomReceiptController')->parameters(['' => 'showroom-receipt']);
            Route::get('confirm/{id}', [
                'as' => 'confirmView',
                'permission' => addAllPermissionShowroom(['showroom-receipt.confirm']),
                'uses' => 'ShowroomReceiptController@confirmView',
            ]);
            Route::post('confirm/{agentReceipt}', [
                'as' => 'confirm',
                'permission' => addAllPermissionShowroom(['showroom-receipt.confirm']),
                'uses' => 'ShowroomReceiptController@confirm',
            ]);
            Route::get('view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionShowroom(['showroom-receipt.index']),
                'uses' => 'ShowroomReceiptController@view',
            ]);
            Route::post('/export-file', [
                'uses' => 'ShowroomReceiptController@getGenerateReceiptProduct',
                'permission' => addAllPermissionShowroom(['showroom-receipt.*']),
                'as' => 'export-file',
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'ShowroomReceiptController@denied',
                'permission' => addAllPermissionShowroom(['showroom-receipt.confirm'])
            ]);
        });
        Route::group(['prefix' => 'showroom-proposal-issues', 'as' => 'showroom-proposal-issue.'], function () {
            Route::resource('', 'ShowroomProposalIssueController')->parameters(['' => 'showroom-proposal-issue']);
            Route::get('approve/{proposal}', [
                'as' => 'approveView',
                'uses' => 'ShowroomProposalIssueController@approveView',
                'permission' => addAllPermissionShowroom(['showroom-proposal-issue.approve'])
            ]);
            Route::post('approve/{proposal}', [
                'as' => 'approve',
                'uses' => 'ShowroomProposalIssueController@approve',
                'permission' => addAllPermissionShowroom(['showroom-proposal-issue.approve'])
            ]);
            Route::get('view/{proposal}', [
                'as' => 'view',
                'uses' => 'ShowroomProposalIssueController@view',
                'permission' => addAllPermissionShowroom(['showroom-proposal-issue.create'])
            ]);
            Route::get('proposal/{id}', [
                'as' => 'proposal',
                'uses' => 'ShowroomProposalIssueController@proposal',
                'permission' => addAllPermissionShowroom(['showroom-proposal-issue.*'])
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'ShowroomProposalIssueController@denied',
                'permission' => addAllPermissionShowroom(['showroom-proposal-issue.approve'])
            ]);
            Route::post('/export-file', [
                'uses' => 'ShowroomProposalIssueController@getGenerateReceiptProduct',
                'permission' => addAllPermissionShowroom(['showroom-proposal-issue.*']),
                'as' => 'export-file',
            ]);
        });
        Route::group(['prefix' => 'showroom-issues', 'as' => 'showroom-issue.'], function () {
            Route::resource('', 'ShowroomIssueController')->parameters(['' => 'showroom-issue']);
            Route::get('confirm/{showroomIssue}', [
                'as' => 'confirmView',
                'uses' => 'ShowroomIssueController@confirmView',
                'permission' => addAllPermissionShowroom(['showroom-issue.confirm'])
            ]);
            Route::post('confirm/{showroomIssue}', [
                'as' => 'confirm',
                'uses' => 'ShowroomIssueController@confirm',
                'permission' => addAllPermissionShowroom(['showroom-issue.confirm'])
            ]);
            Route::get('view/{showroomIssue}', [
                'as' => 'view',
                'uses' => 'ShowroomIssueController@view',
                'permission' => addAllPermissionShowroom(['showroom-issue.index'])
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'ShowroomIssueController@denied',
                'permission' => addAllPermissionShowroom(['showroom-issue.confirm'])
            ]);
            Route::post('/export-file', [
                'uses' => 'ShowroomIssueController@getGenerateReceiptProduct',
                'permission' => addAllPermissionShowroom(['showroom-issue.*']),
                'as' => 'export-file',
            ]);
        });
        Route::group(['prefix' => 'showroom-qr', 'as' => 'showroom-qr.'], function () {
            Route::get('check-qr', [
                'as' => 'check-qr',
                'uses' => 'ShowroomQRController@checkQr',
                'permission' => addAllPermissionShowroom(['showroom-qr.check-qr'])
            ]);
        });
        Route::group(['prefix' => 'showroom-product', 'as' => 'showroom-product.'], function () {
            // Route::resource('', 'ShowroomProductController')->parameters(['' => 'showroom-product'])->except('create,update,store,edit,destrou');
            Route::get('/detail/{id}', [
                'as' => 'detail',
                'permission' => false,
                'uses' => 'ShowroomProductController@detail'
            ]);
            Route::get('/', [
                'as' => 'index',
                'permission' => false,
                'uses' => 'ShowroomProductController@index'
            ]);
            Route::post('/', [
                'as' => 'index',
                'permission' => false,
                'uses' => 'ShowroomProductController@index'
            ]);
        });

        Route::group(['prefix' => 'exchange-goods', 'as' => 'exchange-goods.'], function(){
            Route::get('',[
                'as' => 'index',
                'uses' => 'ExchangeGoodsController@index',
                'permission' => 'exchange-goods.index'
            ]);
            Route::post('',[
                'as' => 'index',
                'uses' => 'ExchangeGoodsController@index',
                'permission' => 'exchange-goods.index'
            ]);
            Route::get('/create',[
                'as' => 'create',
                'uses' => 'ExchangeGoodsController@create',
                'permission' => 'exchange-goods.create'
            ]);

            Route::post('/create',[
                'as' => 'create.store',
                'uses' => 'ExchangeGoodsController@submitCreate',
                'permission' => 'exchange-goods.create'
            ]);

            Route::post('/scan-product',[
                'as' => 'scan-product',
                'uses' => 'ExchangeGoodsController@ajaxPostQrScan',
                'permission' => 'exchange-goods.create'
            ]);
            
            Route::get('/view/{exchange}',[
                'as' => 'view',
                'uses' => 'ExchangeGoodsController@view',
                'permission' => 'exchange-goods.create'
            ]);
        });
    });
});
