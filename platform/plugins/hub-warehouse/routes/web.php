<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\HubWarehouse\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'hub-warehouses', 'as' => 'hub-warehouse.'], function () {
            Route::resource('', 'HubWarehouseController')->parameters(['' => 'hub-warehouse'])->except('destroy');
            Route::get('get-all-hubs', [
                'as' => 'getHub',
                'permission' => false,
                'uses' => 'HubWarehouseController@getAllHub'
            ]);
            Route::get('detail-hub/{id}', [
                'as' => 'detail',
                'permission' => addAllPermissionHub(['hub-warehouse.*']),
                'uses' => 'HubWarehouseController@detail'
            ]);
            Route::post('detail-hub/{id}', [
                'as' => 'detail',
                'permission' => addAllPermissionHub(['hub-warehouse.*']),
                'uses' => 'HubWarehouseController@detail'
            ]);
            Route::get('get-product-by-warehouse/{id}', [
                'uses' => 'HubWarehouseController@getProductByWarehouse',
                'permission' => addAllPermissionHub(['hub-stock.index']),
                'as' => 'getProductByWarehouse'
            ]);
        });
        Route::group(['prefix' => 'hub-stock', 'as' => 'hub-stock.'], function () {
            Route::resource('', 'WarehouseController')->parameters(['' => 'hub-stock'])->except('destroy');
            Route::get('get-warehouse-by-hub/{id}', [
                'as' => 'getWarehouse',
                'permission' => addAllPermissionHub(['hub-stock.index']),
                'uses' => 'WarehouseController@getWarehouseByHub'
            ]);
            Route::get('get-warehouse-by-hub/{id}', [
                'as' => 'getWarehouse',
                'permission' => addAllPermissionHub(['hub-stock.index']),
                'uses' => 'WarehouseController@getWarehouseByHub'
            ]);
            Route::get('get-all-warehouse-hub', [
                'as' => 'getAllWarehouse',
                'permission' => false,
                'uses' => 'WarehouseController@getAllWarehouse'
            ]);
            Route::get('get-quantity-for-warehouse/{id}', [
                'as' => 'checkQuantityBatch',
                'permission' => false,
                'uses' => 'WarehouseController@checkQuantityBatch'
            ]);

            Route::get('create-product-manual/{id}', [
                'as' => 'create-product-manual',
                'uses' => 'WarehouseController@createManual',
                'permission' => addAllPermissionHub(['hub-stock.create-product-manual']),
            ]);
            Route::post('store-product-manual', [
                'as' => 'store-product-manual',
                'uses' => 'WarehouseController@storeManual',
                'permission' => addAllPermissionHub(['hub-stock.create-product-manual']),
            ]);


            Route::get('/detail-odd/{stock}', [
                'as' => 'detail-odd',
                'uses' => 'WarehouseController@detailOddInStock',
                'permission' => addAllPermissionHub(['hub-stock.detail-odd']),
            ]);
            Route::post('/detail-odd/{stock}', [
                'uses' => 'WarehouseController@detailOddInStock',
                'permission' => addAllPermissionHub(['hub-stock.detail-odd']),
            ]);

            Route::get('/detail-batch/{stock}', [
                'as' => 'detail-batch',
                'uses' => 'WarehouseController@detailBatchInStock',
                'permission' => addAllPermissionHub(['hub-stock.*']),
            ]);
            Route::post('/detail-batch/{stock}', [
                'as' => 'detail-batch',
                'uses' => 'WarehouseController@detailBatchInStock',
                'permission' => addAllPermissionHub(['hub-stock.*']),
            ]);
            Route::get('/detail-product/{stock}', [
                'as' => 'detail-product',
                'uses' => 'WarehouseController@detailProductInStock',
                'permission' => addAllPermissionHub(['hub-stock.*']),
            ]);
            Route::post('/detail-product/{stock}', [
                'as' => 'detail-product',
                'uses' => 'WarehouseController@detailProductInStock',
                'permission' => addAllPermissionHub(['hub-stock.*']),
            ]);

            Route::get('/reduce-quantity', [
                'as' => 'reduce-quantity',
                'uses' => 'WarehouseController@reduceQuantity',
                'permission' => addAllPermissionHub(['hub-stock.reduce-quantity']),
            ]);
            Route::post('/reduce-quantity', [
                'uses' => 'WarehouseController@reduceQuantity',
                'permission' => addAllPermissionHub(['hub-stock.reduce-quantity']),
            ]);
        });

        Route::group(['prefix' => 'proposal-hub-issues', 'as' => 'proposal-hub-issue.'], function () {
            Route::resource('', 'ProposalHubIssueController')->parameters(['' => 'proposal-hub-issue']);
            Route::get('/get-product-proposal-issue/{proposal_id}', [
                'as' => 'proposal',
                'permission' => addAllPermissionHub(['proposal-hub-issue.edit']),
                'uses' => 'ProposalHubIssueController@getProductProposal'
            ]);
            Route::get('/approve/{id}', [
                'as' => 'approveProposalProductIssue',
                'permission' => addAllPermissionHub(['proposal-hub-issue.approve']),
                'uses' => 'ProposalHubIssueController@approveProposalProductIssue'
            ]);
            Route::post('/approve/{id}', [
                'as' => 'approve',
                'permission' => addAllPermissionHub(['proposal-hub-issue.approve']),
                'uses' => 'ProposalHubIssueController@approve'
            ]);
            Route::post('/denied/{id}', [
                'as' => 'denied',
                'permission' => addAllPermissionHub(['proposal-hub-issue.approve']),
                'uses' => 'ProposalHubIssueController@denied'
            ]);
            Route::get('/view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionHub(['proposal-hub-issue.*']),
                'uses' => 'ProposalHubIssueController@view'
            ]);

            Route::post('/export-file', [
                'uses' => 'ProposalHubIssueController@getGenerateReceiptProduct',
                'permission' => addAllPermissionHub(['proposal-hub-issue.create']),
                'as' => 'export-file',
            ]);
        });
        Route::group(['prefix' => 'hub-issues', 'as' => 'hub-issue.'], function () {
            Route::resource('', 'HubIssueController')->parameters(['' => 'hub-issue'])->except('create', 'update', 'destroy');
            Route::get('/confirm/{id}', [
                'as' => 'view-confirm',
                'permission' => addAllPermissionHub(['hub-issue.confirm']),
                'uses' => 'HubIssueController@viewConfirm'
            ]);
            Route::post('/confirm/{productIssue}', [
                'as' => 'confirm',
                'permission' => addAllPermissionHub(['hub-issue.confirm']),
                'uses' => 'HubIssueController@confirm'
            ]);
            Route::post('/denied/{id}', [
                'as' => 'denied',
                'permission' => addAllPermissionHub(['hub-issue.confirm']),
                'uses' => 'HubIssueController@denied'
            ]);
            Route::get('/view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionHub(['hub-issue.confirm']),
                'uses' => 'HubIssueController@view'
            ]);
            Route::post('/export-file', [
                'uses' => 'HubIssueController@getGenerateReceiptProduct',
                'permission' => addAllPermissionHub(['hub-issue.*']),
                'as' => 'export-file',
            ]);
            Route::post('/create-batch-issue/{type}', [
                'uses' => 'HubIssueController@createBatchIssue',
                'permission' => addAllPermissionHub(['hub-issue.*']),
                'as' => 'createBatchIssue',
            ]);

            Route::post('/confirm-receipt-in-tour', [
                'uses' => 'HubIssueController@confirmReceiptInTour',
                'permission' => addAllPermissionHub(['hub-issue.*']),
                'as' => 'confirm-receipt-in-tour',
            ]);
        });

        Route::group(['prefix' => 'proposal-hub-receipts', 'as' => 'proposal-hub-receipt.'], function () {
            Route::resource('', 'ProposalHubReceiptController')->parameters(['' => 'proposal-hub-receipt']);
            Route::get('/approve/{id}', [
                'as' => 'approveView',
                'permission' => addAllPermissionHub(['proposal-hub-receipt.approve']),
                'uses' => 'ProposalHubReceiptController@approve'
            ]);

            Route::post('/approve', [
                'as' => 'approve',
                'permission' => addAllPermissionHub(['proposal-hub-receipt.approve']),
                'uses' => 'ProposalHubReceiptController@approveProposal'
            ]);
            Route::get('/view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionHub(['proposal-hub-receipt.*']),
                'uses' => 'ProposalHubReceiptController@view'
            ]);
            Route::get('/get-product-proposal/{proposal_id}', [
                'as' => 'proposal',
                'permission' => addAllPermissionHub(['proposal-hub-receipt.edit']),
                'uses' => 'ProposalHubReceiptController@getProduct'
            ]);
            Route::post('/denied/{id}', [
                'as' => 'denied',
                'permission' => addAllPermissionHub(['proposal-hub-receipt.approve']),
                'uses' => 'ProposalHubReceiptController@denied'
            ]);

            Route::post('/export-file', [
                'uses' => 'ProposalHubReceiptController@getGenerateReceiptProduct',
                'permission' => addAllPermissionHub(['proposal-hub-receipts.create']),
                'as' => 'export-file',
            ]);
        });

        Route::group(['prefix' => 'hub-receipts', 'as' => 'hub-receipt.'], function () {
            Route::resource('', 'HubReceiptController')->parameters(['' => 'hub-receipt'])->except('create', 'update', 'destroy');
            Route::get('/confirm/{id}', [
                'as' => 'confirm',
                'permission' => addAllPermissionHub(['hub-receipt.confirm']),
                'uses' => 'HubReceiptController@confirm'
            ]);
            Route::post('/confirm/{receipt}', [
                'as' => 'confirmReceipt',
                'permission' => addAllPermissionHub(['hub-receipt.confirm']),
                'uses' => 'HubReceiptController@confirmReceipt'
            ]);
            Route::post('/confirmProduct/{receipt}', [
                'as' => 'confirmProduct',
                'permission' => addAllPermissionHub(['hub-receipt.confirm']),
                'uses' => 'HubReceiptController@confirmProduct'
            ]);
            Route::post('/cancel/{receipt}', [
                'as' => 'cancel',
                'permission' => addAllPermissionHub(['hub-receipt.cancel']),
                'uses' => 'HubReceiptController@cancel'
            ]);
            Route::get('/view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionHub(['hub-receipt.index']),
                'uses' => 'HubReceiptController@view'
            ]);
            Route::get('print-qrcode-all-batch/{id}', [
                'uses' => 'HubReceiptController@printQRCodeAll',
                'permission' => addAllPermissionHub(['hub-receipt.*']),
            ])->name('print-qr-code-all');

            Route::post('/export-file', [
                'uses' => 'HubReceiptController@getGenerateReceiptProduct',
                'permission' => addAllPermissionHub(['hub-receipt.confirm']),
                'as' => 'export-file',
            ]);

            Route::get('printQRCode/{id}', [
                'uses' => 'HubReceiptController@printQRCode',
                'permission' => addAllPermissionHub(['hub-receipt.*']),
            ])->name('print-qr-code');
        });
        Route::group(['prefix' => 'hub-products', 'as' => 'hub-product.'], function () {
            Route::resource('', 'HubProductController')->parameters(['' => 'hub-product']);
            Route::get('/detail/{id}', [
                'as' => 'detail',
                'permission' => addAllPermissionHub(['hub-product.*']),
                'uses' => 'HubProductController@detail'
            ]);
        });
    });

});
