<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Warehouse\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Warehouse\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => '/warehouse'], function () {

            //Material
            //Proposal purchase
            Route::group(['prefix' => 'material/proposal'], function () {
                Route::group(['prefix' => 'purchase', 'as' => 'material-proposal-purchase.'], function () {
                    Route::resource('', 'MaterialProposalPurchaseController')->parameters(['' => 'proposal']);

                    Route::get('receipt/{id}', [
                        'uses' => 'MaterialProposalPurchaseController@receiptProposal',
                        'permission' => 'material-proposal-purchase.receipt',
                    ])->name('receipt');

                    Route::get('view/{code}', [
                        'uses' => 'MaterialProposalPurchaseController@viewProposalByCode',
                        'permission' => 'material-proposal-purchase.*',
                    ])->name('view.code');

                    Route::post('cancel/{id}', [
                        'uses' => 'MaterialProposalPurchaseController@cancelProposalPurchase',
                        'permission' => 'material-proposal-purchase.receipt',
                    ])->name('cancel');
                });
            });
            Route::group(['prefix' => 'purchase-goods'], function () {
                Route::group(['prefix' => 'proposal', 'as' => 'proposal-purchase-goods.'], function () {
                    Route::resource('', 'PurchaseGoodsController')->parameters(['' => 'purchase-goods']);

                    Route::get('receipt/{id}', [
                        'uses' => 'PurchaseGoodsController@receiptProposal',
                        'permission' => 'receipt-purchase-goods.receipt',
                    ])->name('receipt');

                    Route::get('view/{code}', [
                        'uses' => 'PurchaseGoodsController@viewProposalByCode',
                        'permission' => 'proposal-purchase-goods.*',
                    ])->name('view.code');

                    Route::post('cancel/{id}', [
                        'uses' => 'PurchaseGoodsController@cancelProposalPurchaseGoods',
                        'permission' => 'receipt-purchase-goods.receipt',
                    ])->name('cancel');
                });

                Route::group(['prefix' => 'receipt-goods', 'as' => 'receipt-purchase-goods.'], function () {
                    Route::resource('', 'ReceiptPurchaseGoodsController')->parameters(['' => 'purchase-goods']);

                    Route::post('{id}', [
                        'uses' => 'ReceiptPurchaseGoodsController@receiptProposal',
                        'permission' => 'receipt-purchase-goods.receipt',
                    ])->name('receipt.confirm');

                    Route::get('{id}', [
                        'uses' => 'ReceiptPurchaseGoodsController@confirmReceipt',
                        'permission' => 'receipt-purchase-goods.confirm',
                    ])->name('confirm');

                    Route::post('confirm/{id}', [
                        'uses' => 'ReceiptPurchaseGoodsController@storeConfirmReceipt',
                        'permission' => 'receipt-purchase-goods.confirm',
                    ])->name('confirm.store');

                    Route::get('view/{id}', [
                        'uses' => 'ReceiptPurchaseGoodsController@viewDetailReceiptGoods',
                        'permission' => 'receipt-purchase-goods.*',
                    ])->name('view');

                    Route::get('status/json', [
                        'as' => 'list.json',
                        'permission' => 'receipt-purchase-goods.confirm',
                        'uses' => 'ReceiptPurchaseGoodsController@getStatusJson',
                    ]);

                    Route::post('status/assign', [
                        'as' => 'assign',
                        'permission' => 'receipt-purchase-goods.confirm',
                        'uses' => 'ReceiptPurchaseGoodsController@postStatusOrder',
                    ]);
                });
            });

            //receipt confirm
            Route::group(['prefix' => 'material/receipt'], function () {
                Route::group(['prefix' => 'confirm', 'as' => 'material-receipt-confirm.'], function () {
                    Route::resource('', 'MaterialReceiptConfirmController')->parameters(['' => 'receipt']);

                    Route::post('{id}', [
                        'uses' => 'MaterialReceiptConfirmController@receiptProposal',
                        'permission' => 'material-proposal-purchase.receipt',
                    ])->name('receipt');

                    Route::get('receipt/{id}', [
                        'uses' => 'MaterialReceiptConfirmController@confirmReceipt',
                        'permission' => 'material-receipt-confirm.confirm',
                    ])->name('confirm');

                    Route::post('receipt/{id}', [
                        'uses' => 'MaterialReceiptConfirmController@storeConfirmReceipt',
                        'permission' => 'material-receipt-confirm.confirm',
                    ])->name('confirm.store');

                    Route::get('view/{code}', [
                        'uses' => 'MaterialReceiptConfirmController@viewReceiptConfirmById',
                        'permission' => 'material-receipt-confirm.*',
                    ])->name('view');

                    Route::get('printQRCode/{id}', [
                        'uses' => 'MaterialReceiptConfirmController@printQRCode',
                        'permission' => 'material-receipt-confirm.*',
                    ])->name('print-qr-code');
                });

                // In phiếu nhập kho
                Route::group(['prefix' => 'material-receipt-pdf', 'as' => 'material-receipt-pdf.'], function () {
                    Route::get('', [
                        'uses' => 'MaterialReceiptTemplatePdfController@index',
                        'permission' => 'material-receipt-confirm.*',
                    ])->name('index');

                    Route::get('preview', [
                        'uses' => 'MaterialReceiptTemplatePdfController@preview',
                        'permission' => 'material-receipt-confirm.*',
                    ])->name('preview');
                    Route::post('reset', [
                        'uses' => 'MaterialReceiptTemplatePdfController@reset',
                        'permission' => 'material-receipt-confirm.*',
                    ])->name('reset');
                    Route::put('update', [
                        'uses' => 'MaterialReceiptTemplatePdfController@update',
                        'permission' => 'material-receipt-confirm.*',
                    ])->name('update');
                    Route::post('export-receipt', [
                        'uses' => 'MaterialReceiptPdfController@getGenerateMatial',
                        'permission' => 'material-receipt-confirm.*',
                    ])->name('export-receipt');
                });
            });

        });

        Route::group(['prefix' => 'materials', 'as' => 'material.'], function () {
            Route::resource('', 'MaterialController')->parameters(['' => 'material'])->except('destroy');
            Route::get('getMaterialByWarehouse/{id}', [
                'uses' => 'MaterialController@getMaterialByWarehouse',
                'permission' => false,
            ]);
            Route::get('getThumbAttribute/{value}', [
                'uses' => 'MaterialController@getThumbAttribute',
                'as' => 'getThumbAttribute',
                'permission' => false,
            ]);
            Route::post('import-material', [
                'uses' => 'MaterialController@import',
                'as' => 'import',
                'permission' => false,
            ]);
            Route::get('detail/{id}', [
                'uses' => 'MaterialController@detail',
                'as' => 'detail',
                'permission' => false,
            ]);
            Route::post('detail/{id}', [
                'uses' => 'MaterialController@detail',
                'as' => 'detail',
                'permission' => false,
            ]);
        });

        //Material batch in stock
        Route::group(['prefix' => 'material-batchs', 'as' => 'material-batch.'], function () {
            Route::resource('', 'MaterialBatchController')->parameters(['' => 'batch']);
            Route::get('detail/{id}', ['as' => 'detail', 'uses' => 'MaterialBatchController@detailBatchInStock', 'permission' => 'material-batch.detail']);
            Route::post('detail/{id}', ['as' => 'detail.post', 'uses' => 'MaterialBatchController@detailBatchInStock', 'permission' => 'material-batch.detail']);

            Route::post('qr-scan', ['uses' => 'MaterialBatchController@qrScan']);

        });

        Route::group(['prefix' => 'type_materials', 'as' => 'type_material.'], function () {
            Route::resource('', 'TypeMaterialController')->parameters(['' => 'type_material']);
        });

        Route::group(['prefix' => 'proposal-goods-issue', 'as' => 'proposal-goods-issue.'], function () {
            Route::resource('', 'MaterialOutController')->parameters(['' => 'material_plan']);
            Route::get('plan-material/{id}', [
                'uses' => 'MaterialOutController@plan_material',
                'permission' => false,
            ]);
            Route::get('getMaterialOutInfo/{id}', ['uses' => 'MaterialOutController@getMaterialOutInfo',
                'permission' => 'proposal-goods-issue.edit']);
            Route::get('plan-material/{id}/{material_id}', 'MaterialOutController@plan_material_id');
            Route::get('check-quantity-stock/{id}', 'MaterialOutController@check_quantity_stock');


            Route::get('code/{code}', [
                'uses' => 'MaterialOutController@editPlanByCode',
                'permission' => 'proposal-goods-issue.edit',
            ])->name('plan.edit');
            Route::post('code', [
                'uses' => 'MaterialOutController@storePlanAndUpdate',
                'permission' => 'proposal-goods-issue.edit',
            ])->name('plan.store');

            Route::post('denied/{id}', [
                'uses' => 'MaterialOutController@deniedMaterialOut',
                'permission' => 'proposal-goods-issue.receipt',
            ])->name('denied');
            //Get list out material
            Route::get('list-out-material/{id}', [
                'uses' => 'MaterialOutController@listOutMaterial',
                'permission' => 'proposal-goods-issue.receipt',
            ])->name('list.out');

            //Receipt material plan out
            Route::post('approve/{id}', [
                'uses' => 'MaterialOutController@approveProposalGoodIssue',
                'permission' => 'proposal-goods-issue.receipt',
            ])->name('receipt');
            //

            Route::get('view/{code}', [
                'uses' => 'MaterialOutController@viewProposalByCode',
                'permission' => 'proposal-goods-issue.index',
            ])->name('view.code');
        });
        //
        Route::group(['prefix' => 'goods-issue', 'as' => 'goods-issue-receipt.'], function () {
            Route::resource('', 'MaterialOutReceiptController')->parameters(['' => 'receipt']);

            Route::post('{id}', [
                'uses' => 'MaterialOutReceiptController@receiptProposal',
                'permission' => 'material-proposal-purchase.receipt',
            ])->name('receipt');

            // In phiếu xuất kho
            Route::group(['prefix' => 'material-issue', 'as' => 'issue.'], function () {
                Route::get('', [
                    'uses' => 'MaterialIssueTemplatePdfController@index',
                    'permission' => 'goods-issue-receipt.confirm',
                ])->name('index');

                Route::get('preview', [
                    'uses' => 'MaterialIssueTemplatePdfController@preview',
                    'permission' => 'goods-issue-receipt.confirm',
                ])->name('preview');
                Route::post('reset', [
                    'uses' => 'MaterialIssueTemplatePdfController@reset',
                    'permission' => 'goods-issue-receipt.confirm',
                ])->name('reset');
                Route::put('update', [
                    'uses' => 'MaterialIssueTemplatePdfController@update',
                    'permission' => 'goods-issue-receipt.confirm',
                ])->name('update');
                Route::post('export-receipt', [
                    'uses' => 'MaterialIssuePdfController@getGenerateMatial',
                    'permission' => 'goods-issue-receipt.*',
                ])->name('export-receipt');
            });

            Route::get('issue/{id}', [
                'uses' => 'MaterialOutReceiptController@confirmReceipt',
                'permission' => 'goods-issue-receipt.confirm',
            ])->name('issue');

            Route::post('confirm/{id}', [
                'uses' => 'MaterialOutReceiptController@confirmGoodIssue',
                'permission' => 'goods-issue-receipt.confirm',
            ])->name('confirm');

            Route::get('/get-more-quantity', [
                'uses' => 'MaterialOutReceiptController@getMoreQuantity',
                'permission' => 'goods-issue-receipt.confirm',
            ])->name('getMoreQuantity');

            Route::get('view/{id}', [
                'uses' => 'MaterialOutReceiptController@viewReceiptById',
                'permission' => 'goods-issue-receipt.*',
            ])->name('view');
        });

        Route::get('/get-user-id', [
            'permission' => false,
            'uses' => function () {
                $user = Auth::user()->permissions;
                return response()->json(['user' => $user]);
            }
        ]);

        Route::group(['prefix' => 'suppliers', 'as' => 'supplier.'], function () {
            Route::resource('', 'SupplierController')->parameters(['' => 'supplier']);
            Route::get('/getall', [
                'permission' => false,
                'uses' => function () {
                    $user = Supplier::all();
                    return response()->json(['user' => $user]);
                }
            ]);
        });

        Route::group(['prefix' => 'warehouse-material', 'as' => 'warehouse-material.'], function () {
            Route::resource('', 'WareHouseMaterialController')->parameters(['' => 'warehouse-material']);
            Route::get('detail/{id}', [
                'as' => 'detail',
                'uses' => 'WareHouseMaterialController@detail',
                'permission' => 'warehouse-material.detail'
            ]);
            Route::post('detail/{id}', [
                'as' => 'detail',
                'uses' => 'WareHouseMaterialController@detail',
                'permission' => 'warehouse-material.detail'
            ]);
        });



        //Receipt proposal inventory
        Route::group(['prefix' => 'receipt-inventory', 'as' => 'receipt-inventory.'], function () {
            Route::resource('', 'ReceiptInventoryController')->parameters(['' => 'receipt-inventory']);

            Route::get('code/{code}', [
                'uses' => 'ReceiptInventoryController@receiptByCode',
                'permission' => 'receipt-inventory.code',
            ])->name('code');

            Route::post('create/stock', [
                'uses' => 'ReceiptInventoryController@storeInventoryStock',
                'permission' => 'receipt-inventory.code',
            ])->name('create.stock');

            Route::get('generate-invoice/{code}', [
                'uses' => 'ReceiptInventoryController@getGenerateInvoice',
                'permission' => 'receipt-inventory.code',
            ]);
        });

        // Actual receipt
        Route::group(['prefix' => 'actualouts', 'as' => 'actualout.'], function () {


            Route::group(['prefix' => 'material/outs'], function () {

                Route::group(['prefix' => 'confirm', 'as' => 'material-out-confirm.'], function () {
                    Route::resource('', 'ActualOutController')->parameters(['' => 'actualout']);
                    Route::get('out-goods/{id}', [
                        'uses' => 'ActualOutController@confirmReceipt',
                        'as' => 'out-goods'
                        // 'permission' => 'out-receipt.code',
                    ]);
                    Route::post('actual-out-material/{id}', [
                        'uses' => 'ActualOutController@storeConfirmReceipt',
                        'as' => 'actual-out-material.store',
                        // 'permission' => 'out-receipt.code',
                    ]);
                });
            });

        });

        Route::group(['prefix' => 'processing_houses', 'as' => 'processing_house.'], function () {
            Route::resource('', 'ProcessingHouseController')->parameters(['' => 'processing_house']);
        });

    });

});
