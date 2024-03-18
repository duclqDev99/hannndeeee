<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\SaleWarehouse\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'sale-warehouses', 'as' => 'sale-warehouse.'], function () {
            Route::resource('', 'SaleWarehouseController')->parameters(['' => 'sale-warehouse'])->except('destroy');
        });
        Route::group(['prefix' => 'sale-warehouse-children', 'as' => 'sale-warehouse-child.'], function () {
            Route::resource('', 'SaleWarehouseChildController')->parameters(['' => 'sale-warehouse-child'])->except('destroy');
            Route::get('get-all-warehouse', [
                'as' => 'getAllWarehouse',
                'permission' => false,
                'uses' => 'SaleWarehouseChildController@getAllWarehouse',
            ]);
        });
        Route::group(['prefix' => 'sale-warehouse-product', 'as' => 'sale-warehouse-product.'], function () {
            Route::get('/', [
                'as' => 'index',
                'permission' => addAllPermissionSaleWarehouse(['sale-product.index']),
                'uses' => 'SaleWarehouseProductController@index',
            ]);
            Route::post('/', [
                'as' => 'index',
                'permission' => addAllPermissionSaleWarehouse(['sale-product.index']),
                'uses' => 'SaleWarehouseProductController@index',
            ]);
        });
        Route::group(['prefix' => 'sale-receipts', 'as' => 'sale-receipt.'], function () {
            Route::resource('', 'SaleReceiptController')->parameters(['' => 'sale-receipt']);
            Route::get('confirm/{id}', [
                'as' => 'confirmView',
                'permission' => addAllPermissionSaleWarehouse(['sale-receipt.confirm']),
                'uses' => 'SaleReceiptController@confirmView',
            ]);
            Route::post('confirm/{saleReceipt}', [
                'as' => 'confirm',
                'permission' => addAllPermissionSaleWarehouse(['sale-receipt.confirm']),
                'uses' => 'SaleReceiptController@confirm',
            ]);
            Route::get('view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionSaleWarehouse(['sale-receipt.index']),
                'uses' => 'SaleReceiptController@view',
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'permission' => addAllPermissionSaleWarehouse(['sale-receipt.denied']),
                'uses' => 'SaleReceiptController@denied',
            ]);
        });
        Route::group(['prefix' => 'sale-proposal-issues', 'as' => 'sale-proposal-issue.'], function () {
            Route::resource('', 'SaleProposalIssueController')->parameters(['' => 'sale-proposal-issue']);
            Route::get('proposal/{id}', [
                'as' => 'proposal',
                'permission' => addAllPermissionSaleWarehouse(['sale-proposal-issue.edit']),
                'uses' => 'SaleProposalIssueController@proposal',
            ]);
            Route::get('approve/{id}', [
                'as' => 'approveView',
                'permission' => addAllPermissionSaleWarehouse(['sale-proposal-issue.approve']),
                'uses' => 'SaleProposalIssueController@approveView',
            ]);
            Route::post('approve/{id}', [
                'as' => 'approve',
                'permission' => addAllPermissionSaleWarehouse(['sale-proposal-issue.approve']),
                'uses' => 'SaleProposalIssueController@approve',
            ]);

            Route::get('view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionSaleWarehouse(['sale-proposal-issue.index']),
                'uses' => 'SaleProposalIssueController@view',
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'permission' => addAllPermissionSaleWarehouse(['sale-proposal-issue.denied']),
                'uses' => 'SaleProposalIssueController@denied',
            ]);

        });

        Route::group(['prefix' => 'sale-issues', 'as' => 'sale-issue.'], function () {
            Route::resource('', 'SaleIssueController')->parameters(['' => 'sale-issue'])->except([
                'create',
                'store',
                'edit',
                'update',
                'destroy'
            ]);
            Route::get('confirm/{id}', [
                'as' => 'view-confirm',
                'permission' => addAllPermissionSaleWarehouse(['sale-issue.confirm']),
                'uses' => 'SaleIssueController@viewConfirm',
            ]);
            Route::get('view/{id}', [
                'as' => 'view',
                'permission' => addAllPermissionSaleWarehouse(['sale-issue.index']),
                'uses' => 'SaleIssueController@view',
            ]);
            Route::post('confirm/{id}', [
                'as' => 'confirm',
                'permission' => addAllPermissionSaleWarehouse(['sale-issue.confirm']),
                'uses' => 'SaleIssueController@confirm',
            ]);
            Route::post('/create-batch-issue/{type}', [
                'uses' => 'SaleIssueController@createBatchIssue',
                'permission' => addAllPermissionSaleWarehouse((['sale-issue.index'])),
                'as' => 'createBatchIssue',
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'permission' => addAllPermissionSaleWarehouse(['sale-issue.denied']),
                'uses' => 'SaleIssueController@denied',
            ]);
            Route::post('/confirm-receipt-in-tour', [
                'uses' => 'SaleIssueController@confirmReceiptInTour',
                'permission' => addAllPermissionHub(['sale-issue.confirm']),
                'as' => 'confirm-receipt-in-tour',
            ]);
        });
    });
});
