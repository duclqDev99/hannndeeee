<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Agent\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'agents', 'as' => 'agent.'], function () {
            Route::resource('', 'AgentController')->parameters(['' => 'agent'])->except('destroy');
            Route::get('get-all-agent', [
                'as' => 'get-all-agent',
                'uses' => 'AgentController@getAllAgent',
                'permission' => false
            ]);
            Route::get('get-agent-warehouse', [
                'as' => 'get-agent-warehouse',
                'uses' => 'AgentWarehouseController@getAgentWarehouse',
                'permission' => false
            ]);
        });
        Route::group(['prefix' => 'agent-warehouse', 'as' => 'agent-warehouse.'], function () {
            Route::resource('', 'AgentWarehouseController')->parameters(['' => 'agent-warehouse'])->except('destroy');

            Route::get('create-product-manual/{id}', [
                'as' => 'create-product-manual',
                'uses' => 'AgentWarehouseController@createManual',
            ]);
            Route::post('store-product-manual', [
                'as' => 'store-product-manual',
                'uses' => 'AgentWarehouseController@storeManual',
            ]);
            Route::get('get-warehouse-by-agent/{id}', [
                'as' => 'get-warehouse-by-agent',
                'uses' => 'AgentWarehouseController@getWarehouseByAgent',
                'permission' => false

            ]);
            Route::get('get-all-agent-warehouse', [
                'as' => 'get-all-agent-warehouse',
                'uses' => 'AgentWarehouseController@getAllAgentWarehouse',
                'permission' => false

            ]);

            //Xem chi tiết lô hàng có trong kho
            Route::get('/detail-batch/{stock}', [
                'as' => 'detail-batch',
                'uses' => 'AgentWarehouseController@detailBatchInStock',
                'permission' => addAllPermissionAgent(['agent-warehouse.detail-batch']),
            ]);
            Route::post('/detail-batch/{stock}', [
                'uses' => 'AgentWarehouseController@detailBatchInStock',
                'permission' => addAllPermissionAgent(['agent-warehouse.detail-batch']),
            ]);

            //Xem chi tiết san phảm hàng có trong kho
            Route::get('/detail-odd/{stock}', [
                'as' => 'detail-odd',
                'uses' => 'AgentWarehouseController@detailOddInStock',
                'permission' => addAllPermissionAgent(['agent-warehouse.detail-odd']),
            ]);
            Route::post('/detail-odd/{stock}', [
                'uses' => 'AgentWarehouseController@detailOddInStock',
                'permission' => addAllPermissionAgent(['agent-warehouse.detail-odd']),
            ]);
            Route::get('/reduce-quantity', [
                'as' => 'reduce-quantity',
                'uses' => 'AgentWarehouseController@reduceQuantity',
                'permission' => addAllPermissionAgent(['agent-warehouse.reduce-quantity']),
            ]);
            Route::post('/reduce-quantity', [
                'uses' => 'AgentWarehouseController@reduceQuantity',
                'permission' => addAllPermissionAgent(['agent-warehouse.reduce-quantity']),
            ]);
        });
        Route::group(['prefix' => 'agent-product', 'as' => 'agent-product.'], function () {
            Route::resource('', 'AgentProductController')->parameters(['' => 'agent-product']);
            Route::get('/detail/{id}', [
                'as' => 'detail',
                'permission' => 'agent-product.*',
                'uses' => 'AgentProductController@detail'
            ]);
        });
        Route::get('agent/get-list-agent-for-user', [
            'as' => 'agent.get-list-agent-for-user',
            'uses' => 'AgentController@getListAgentForUser'
        ]);
        Route::group(['prefix' => 'proposal-agent-receipts', 'as' => 'proposal-agent-receipt.'], function () {
            Route::resource('', 'AgentProposalReceiptController')->parameters(['' => 'proposal-agent-receipt']);
            Route::get('proposal/{id}', [
                'as' => 'proposal',
                'uses' => 'AgentProposalReceiptController@proposal',
                'permission' => false
            ]);
            // Route::get('approve/{proposal}', [
            //     'as' => 'approve',
            //     'uses' => 'AgentProposalReceiptController@approveView',
            //     'permission' => addAllPermissionAgent(['proposal-agent-receipt.approve'])
            // ]);
            Route::get('view/{id}', [
                'as' => 'view',
                'uses' => 'AgentProposalReceiptController@view',
                'permission' => addAllPermissionAgent(['proposal-agent-receipt.index'])
            ]);
            // Route::post('approve/{proposal}', [
            //     'as' => 'postApprove',
            //     'uses' => 'AgentProposalReceiptController@approve',
            //     'permission' => addAllPermissionAgent(['proposal-agent-receipt.approve'])
            // ]);
            Route::post('/export-file', [
                'uses' => 'AgentProposalReceiptController@getGenerateReceiptProduct',
                'permission' => addAllPermissionAgent(['proposal-agent-receipt.*']),
                'as' => 'export-file',
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'AgentProposalReceiptController@denied',
                'permission' => addAllPermissionAgent(['proposal-agent-receipt.approve'])
            ]);
            Route::get('get-product-in-hub/{id}', [
                'as' => 'get-product-in-hub',
                'uses' => 'AgentProposalReceiptController@getProductInHub',
                'permission' => false
            ]);
        });
        Route::group(['prefix' => 'agent-receipts', 'as' => 'agent-receipt.'], function () {
            Route::resource('', 'AgentReceiptController')->parameters(['' => 'agent-receipt'])->except('create', 'update', 'destroy');

            Route::get('confirm/{id}', [
                'as' => 'confirmView',
                'permission' => addAllPermissionAgent(['agent-receipt.confirm']),
                'uses' => 'AgentReceiptController@confirmView',
            ]);
            Route::post('confirm/{agentReceipt}', [
                'as' => 'confirm',
                'permission' => addAllPermissionAgent(['agent-receipt.confirm']),
                'uses' => 'AgentReceiptController@confirm',
            ]);
            Route::get('view/{agentReceipt}', [
                'as' => 'view',
                'permission' => addAllPermissionAgent(['agent-receipt.index']),
                'uses' => 'AgentReceiptController@view',
            ]);
            Route::post('/export-file', [
                'uses' => 'AgentReceiptController@getGenerateReceiptProduct',
                'permission' => addAllPermissionAgent(['agent-receipt.*']),
                'as' => 'export-file',
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'AgentReceiptController@denied',
                'permission' => addAllPermissionAgent(['agent-receipt.denied'])
            ]);
        });
        Route::group(['prefix' => 'agent-proposal-issues', 'as' => 'agent-proposal-issue.'], function () {
            Route::resource('', 'AgentProposalIssueController')->parameters(['' => 'agent-proposal-issue']);
            Route::get('approve/{proposal}', [
                'as' => 'approveAgentProposal',
                'uses' => 'AgentProposalIssueController@approveAgentProposal',
                'permission' => addAllPermissionAgent(['proposal-agent-issue.approve'])
            ]);
            Route::post('approve/{proposal}', [
                'as' => 'approve',
                'uses' => 'AgentProposalIssueController@approve',
                'permission' => addAllPermissionAgent(['proposal-agent-issue.approve'])
            ]);
            Route::get('view/{proposal}', [
                'as' => 'view',
                'uses' => 'AgentProposalIssueController@view',
                'permission' => addAllPermissionAgent(['proposal-agent-issue.create'])
            ]);
            Route::get('proposal/{id}', [
                'as' => 'proposal',
                'uses' => 'AgentProposalIssueController@proposal',
                'permission' => addAllPermissionAgent(['proposal-agent-issue.*'])
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'AgentProposalIssueController@denied',
                'permission' => addAllPermissionAgent(['proposal-agent-issue.approve'])
            ]);

            Route::post('/export-file', [
                'uses' => 'AgentProposalIssueController@getGenerateReceiptProduct',
                'permission' => addAllPermissionAgent(['proposal-agent-receipt.*']),
                'as' => 'export-file',
            ]);
        });
        Route::group(['prefix' => 'agent-issues', 'as' => 'agent-issue.'], function () {
            Route::resource('', 'AgentIssueController')->parameters(['' => 'agent-issue']);
            Route::get('confirm/{agentIssue}', [
                'as' => 'confirmView',
                'uses' => 'AgentIssueController@confirmView',
                'permission' => addAllPermissionAgent(['agent-issue.confirm'])
            ]);
            Route::post('confirm/{agentIssue}', [
                'as' => 'confirm',
                'uses' => 'AgentIssueController@confirm',
                'permission' => addAllPermissionAgent(['agent-issue.confirm'])
            ]);
            Route::get('view/{agentIssue}', [
                'as' => 'view',
                'uses' => 'AgentIssueController@view',
                'permission' => addAllPermissionAgent(['agent-issue.index'])
            ]);
            Route::post('denied/{id}', [
                'as' => 'denied',
                'uses' => 'AgentIssueController@denied',
                'permission' => addAllPermissionAgent(['agent-issue.confirm'])
            ]);

            Route::post('/export-file', [
                'uses' => 'AgentIssueController@getGenerateReceiptProduct',
                'permission' => addAllPermissionAgent(['agent-issue.*']),
                'as' => 'export-file',
            ]);
        });
    });

});
