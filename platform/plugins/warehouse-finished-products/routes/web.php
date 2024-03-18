<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\WarehouseFinishedProducts\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'warehouse-finished-products', 'as' => 'warehouse-finished-products.'], function () {
            Route::resource('', 'WarehouseFinishedProductsController')->parameters(['' => 'warehouse-finished-products']);
            // xem san phẩm có trong kho
            Route::get('get-all-warehouse', [
                'uses' => 'WarehouseFinishedProductsController@getAllWarehouse',
                'as' => 'getAllWarehouse',
                'permission' => false
            ]);

            //Xem chi tiết lô hàng có trong kho
            Route::get('/detail/{stock}', [
                'uses' => 'WarehouseFinishedProductsController@detailBatchInStock',
                'permission' => 'warehouse-finished-products.*',
                'as' => 'detail',
            ]);
            Route::post('/detail/{stock}', [
                'uses' => 'WarehouseFinishedProductsController@detailBatchInStock',
                'permission' => 'warehouse-finished-products.*',
            ]);

            Route::get('/detail-odd/{stock}', [
                'as' => 'detail-odd',
                'uses' => 'WarehouseFinishedProductsController@detailOddInStock',
                'permission' => 'warehouse-finished-products.detail-odd',

                // 'permission' => 'detail-odd',
            ]);
            Route::post('/detail-odd/{stock}', [
                'uses' => 'WarehouseFinishedProductsController@detailOddInStock',
                'permission' => 'warehouse-finished-products.detail-odd',
            ]);

            Route::get('/reduce-quantity', [
                'as' => 'reduce-quantity',
                'uses' => 'WarehouseFinishedProductsController@reduceQuantity',
                'permission' => 'warehouse-finished-products.reduce-quantity',
            ]);
            Route::post('/reduce-quantity', [
                'as' => 'reduce-quantity',
                'uses' => 'WarehouseFinishedProductsController@reduceQuantity',
                'permission' => 'warehouse-finished-products.reduce-quantity',
            ]);

            Route::get('create-product-manual/{id}', [
                'as' => 'create-product-manual',
                'uses' => 'WarehouseFinishedProductsController@createManual',
                'permission' => 'warehouse-finished-products.create-product-manual',
            ]);
            Route::post('create-product-manual/{id}', [
                'as' => 'create-product-manual',
                'uses' => 'WarehouseFinishedProductsController@createManual',
                'permission' => 'warehouse-finished-products.create-product-manual',
            ]);
            Route::post('store-product-manual', [
                'as' => 'store-product-manual',
                'uses' => 'WarehouseFinishedProductsController@storeManual',
                'permission' => 'warehouse-finished-products.create-product-manual',
            ]);
        });
        Route::group(['prefix' => 'finished-products', 'as' => 'finished-products.'], function () {
            Route::resource('', 'FinishedProductsController')->parameters(['' => 'finished-products'])->except(['create', 'store', 'edit', 'update']);

            Route::get('getAllProduct/{id}/{type?}', [
                'uses' => 'FinishedProductsController@getAllProduct',
                'permission' => 'warehouse-finished-products.index',
                'as' => 'getAllProduct'
            ]);
            Route::get('get-product-for-batch-in-warehouse/{id}', [
                'uses' => 'FinishedProductsController@getProductForWarehouseInBatch',
                'permission' => 'warehouse-finished-products.index',
                'as' => 'getProductForWarehouseInBatch'
            ]);
            Route::get('getAllListProduct/{id?}', [
                'uses' => 'FinishedProductsController@getAllListProduct',
                'permission' => false,
                'as' => 'getAllListProduct'
            ]);

            Route::get('detail/{id}', [
                'uses' => 'FinishedProductsController@detail',
                'permission' => false,
                'as' => 'detail'
            ]);
            Route::post('detail/{id}', [
                'uses' => 'FinishedProductsController@detail',
                'permission' => false,
                'as' => 'detail'
            ]);
            Route::get('checkQuantity/{warehouseId}/{id}', [
                'uses' => 'FinishedProductsController@checkQuantity',
                'permission' => 'warehouse-finished-products.index',
                'as' => 'checkQuantity'
            ]);

        });

        Route::group(['prefix' => 'proposal-product-issue', 'as' => 'proposal-product-issue.'], function () {
            Route::resource('', 'ProposalProductIssueController')->parameters(['' => 'proposal-product-issue']);
            Route::get('approve-proposal-product-issue/{id}', [
                'uses' => 'ProposalProductIssueController@approveProposalProductIssue',
                'as' => 'approveProposalProductIssue',
                'permission' => ['proposal-product-issue.approve', 'warehouse-finished-products.warehouse-all','proposal-product-issue.examine']
            ]);
            Route::post('denied-proposal-product-issue/{id}', [
                'uses' => 'ProposalProductIssueController@rejectProposalProductIssue',
                'as' => 'denied',
                'permission' =>['proposal-product-issue.examine', 'warehouse-finished-products.warehouse-all']
            ]);
            Route::post('approve-proposal-product-issue/{proposal}', [
                'uses' => 'ProposalProductIssueController@storeApproveProposalProductIssue',
                'as' => 'approve',
                'permission' => ['proposal-product-issue.approve', 'warehouse-finished-products.warehouse-all']
            ]);
            Route::get('view/{id}', [
                'uses' => 'ProposalProductIssueController@viewProposalProductIssue',
                'as' => 'view',
                'permission' => ['proposal-product-issue.index', 'warehouse-finished-products.warehouse-all']
            ]);
            Route::get('get-product/{id}', [
                'uses' => 'ProposalProductIssueController@getProductProposal',
                'as' => 'getProduct',
                'permission' => 'proposal-product-issue.index'
            ]);
            Route::post('examine-proposal/{proposal}', [
                'uses' => 'ProposalProductIssueController@examineProposal',
                'as' => 'examine',
                'permission' =>['proposal-product-issue.examine', 'warehouse-finished-products.warehouse-all']
            ]);
            Route::post('/export/{id}', [
                'uses' => 'ProposalProductIssueController@exportProposalProductIssue',
                'permission' => 'proposal-product-issue.index',
                'as' => 'proposal.export',
            ]);

        });

        Route::group(['prefix' => 'proposal-receipt-products', 'as' => 'proposal-receipt-products.'], function () {
            Route::resource('', 'ProposalReceiptProductController')->parameters(['' => 'proposal-receipt-products']);

            Route::get('/approved/{proposal}', [
                'uses' => 'ProposalReceiptProductController@censorshipProposalReceiptProduct',
                'permission' => ['proposal-receipt-products.censorship', 'warehouse-finished-products.warehouse-all'],
                'as' => 'censorship',
            ]);
            Route::post('/approved/{proposal}', [
                'uses' => 'ProposalReceiptProductController@approvedProposalReceiptProduct',
                'permission' => ['proposal-receipt-products.censorship', 'warehouse-finished-products.warehouse-all'],
                'as' => 'approved',
            ]);
            Route::get(
                'get-proposal/{id}',
                [
                    'uses' => 'ProposalReceiptProductController@getProposal',
                    'permission' => ['proposal-receipt-products.edit', 'warehouse-finished-products.warehouse-all'],
                    'as' => 'getProposal',
                ]
            );

            //Huỷ đơn đề xuất nhập kho
            Route::post('/cancel/{proposal}', [
                'uses' => 'ProposalReceiptProductController@cancelProposalReceiptProduct',
                'permission' => ['proposal-receipt-products.censorship', 'warehouse-finished-products.warehouse-all'],
                'as' => 'cancel',
            ]);

            //Huỷ đơn đề xuất nhập kho
            Route::get('/view/{proposal}', [
                'uses' => 'ProposalReceiptProductController@viewProposalReceiptProduct',
                'permission' => 'proposal-receipt-products.index',
                'as' => 'view',
            ]);

            Route::post('/export-file', [
                'uses' => 'ProposalReceiptProductController@getGenerateReceiptProduct',
                'permission' => ['proposal-receipt-products.*'],
                'as' => 'export-file',
            ]);
        });

        Route::group(['prefix' => 'product-issue', 'as' => 'product-issue.'], function () {
            Route::resource('', 'ProductIssueController')->parameters(['' => 'product-issue'])->except('create', 'update', 'delete');
            Route::get('confirm-product-issue/{id}', [
                'uses' => 'ProductIssueController@viewConfirm',
                'as' => 'view-confirm',
                'permission' => ['product-issue.confirm', 'warehouse-finished-products.warehouse-all']
            ]);
            Route::post('confirm-product-issue/{id}', [
                'uses' => 'ProductIssueController@confirmProductIssue',
                'as' => 'confirm',
                'permission' => ['product-issue.confirm', 'warehouse-finished-products.warehouse-all']
            ]);
            Route::post('denied-product-issue/{id}', [
                'uses' => 'ProductIssueController@deniedProductIssue',
                'as' => 'denied',
                'permission' => ['product-issue.denied', 'warehouse-finished-products.warehouse-all']
            ]);
            Route::get('/view/{id}', [
                'uses' => 'ProductIssueController@viewProductIssueDetail',
                'permission' => ['product-issue.index', 'warehouse-finished-products.warehouse-all'],
                'as' => 'view',
            ]);
            Route::post('/export/{id}', [
                'uses' => 'ProductIssueController@exportProductIssueDetail',
                'permission' => ['product-issue.index', 'warehouse-finished-products.warehouse-all'],
                'as' => 'issue.export',
            ]);
            Route::get('/get-more-quantity', [
                'uses' => 'ProductIssueController@getMoreQuantity',
                'permission' => ['product-issue.confirm', 'warehouse-finished-products.warehouse-all']
            ])->name('getMoreQuantity');
        });

        Route::group(['prefix' => 'hubs', 'as' => 'hub.'], function () {
            Route::resource('', 'HubController')->parameters(['' => 'hub']);
            Route::get('get-all-hubs', [
                'uses' => 'HubController@getAllHubs',
                'as' => 'getAllHubs',
                'permission' => false
            ]);
        });

        Route::group(['prefix' => 'receipt-product', 'as' => 'receipt-product.'], function () {
            Route::resource('', 'ReceiptProductController')->parameters(['' => 'receipt-product']);

            Route::get('/approved/{receipt}', [
                'uses' => 'ReceiptProductController@censorshipReceiptProduct',
                'permission' => ['receipt-product.censorship', 'warehouse-finished-products.warehouse-all'],
                'as' => 'censorship',
            ]);

            Route::post('/approved/{receipt}', [
                'uses' => 'ReceiptProductController@approvedReceiptProduct',
                'permission' => ['receipt-product.censorship', 'warehouse-finished-products.warehouse-all'],
                'as' => 'approved',
            ]);

            Route::get('/view/{receipt}', [
                'uses' => 'ReceiptProductController@viewReceiptProduct',
                'permission' => ['receipt-product.*', 'warehouse-finished-products.warehouse-all'],
                'as' => 'view',
            ]);

            Route::post('/export-file', [
                'uses' => 'ReceiptProductController@getGenerateReceiptProduct',
                'permission' => ['receipt-product.*', 'warehouse-finished-products.warehouse-all'],
                'as' => 'export-file',
            ]);

            Route::get('printQRCode/{id}', [
                'uses' => 'ReceiptProductController@printQRCode',
                'permission' => ['receipt-product.*', 'warehouse-finished-products.warehouse-all'],
            ])->name('print-qr-code');

            Route::get('print-qrcode-all-batch/{id}', [
                'uses' => 'ReceiptProductController@printQRCodeAll',
                'permission' => ['receipt-product.*', 'warehouse-finished-products.warehouse-all'],
            ])->name('print-qr-code-all');

            Route::post(
                'ajax-post-qr-scan',
                [
                    'uses' => 'ReceiptProductController@ajaxPostQrScan',
                    'permission' => 'receipt-product.*',
                ]
            )->name('ajax-post-batch-qr-scan');


            Route::post('cancel-receipt/{receipt}', [
                'uses' => 'ReceiptProductController@cancelReceiptProduct',
                'permission' => ['receipt-product.cancel', 'warehouse-finished-products.warehouse-all'],
            ])->name('cancel');

            Route::delete('delete-widget', [
                'as' => 'widgets.destroy',
                'uses' => '\Botble\Widget\Http\Controllers\WidgetController@destroy',
                'permission' => false,
            ]);

            Route::post(
                'get-batch-info',
                'ReceiptProductController@ajaxGetBatchInfo'
            )->name('ajax-post-batch-qr-scan');


        });

    });

});
