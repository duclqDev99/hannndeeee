<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Agent\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'agent-orders', 'as' => 'agent.orders.'], function () {
            Route::resource('', 'AgentOrderController')->parameters(['' => 'order']);

            Route::get('detail/{id}', [
                'as' => 'detail',
                'uses' => 'AgentOrderController@detail',
                'permission' => addAllPermissionAgent(['agent.orders.index']),
            ]);

            Route::get('reorder', [
                'as' => 'reorder',
                'uses' => 'AgentOrderController@getReorder',
                'permission' => addAllPermissionAgent(['orders.create']),
            ]);

            Route::get('generate-invoice/{order}', [
                'as' => 'generate-invoice',
                'uses' => 'AgentOrderController@getGenerateInvoice',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('confirm', [
                'as' => 'confirm',
                'uses' => 'AgentOrderController@postConfirm',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ]);

            Route::post('send-order-confirmation-email/{order}', [
                'as' => 'send-order-confirmation-email',
                'uses' => 'AgentOrderController@postResendOrderConfirmationEmail',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('create-shipment/{order}', [
                'as' => 'create-shipment',
                'uses' => 'AgentOrderController@postCreateShipment',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('cancel-shipment/{shipment}', [
                'as' => 'cancel-shipment',
                'uses' => 'AgentOrderController@postCancelShipment',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('update-shipping-address/{address}', [
                'as' => 'update-shipping-address',
                'uses' => 'AgentOrderController@postUpdateShippingAddress',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('update-tax-information/{taxInformation}', [
                'as' => 'update-tax-information',
                'uses' => 'AgentOrderController@postUpdateTaxInformation',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('cancel-order/{order}', [
                'as' => 'cancel',
                'uses' => 'AgentOrderController@postCancelOrder',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::get('print-shipping-order/{order}', [
                'as' => 'print-shipping-order',
                'uses' => 'AgentOrderController@getPrintShippingOrder',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('confirm-payment/{order}', [
                'as' => 'confirm-payment',
                'uses' => 'AgentOrderController@postConfirmPayment',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::get('get-shipment-form/{order}', [
                'as' => 'get-shipment-form',
                'uses' => 'AgentOrderController@getShipmentForm',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('refund/{order}', [
                'as' => 'refund',
                'uses' => 'AgentOrderController@postRefund',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey();

            Route::get('get-available-shipping-methods', [
                'as' => 'get-available-shipping-methods',
                'uses' => 'AgentOrderController@getAvailableShippingMethods',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ]);

            Route::post('coupon/apply', [
                'as' => 'apply-coupon-when-creating-order',
                'uses' => 'AgentOrderController@postApplyCoupon',
                'permission' => addAllPermissionAgent(['orders.create']),
            ]);

            Route::post('check-data-before-create-order', [
                'as' => 'check-data-before-create-order',
                'uses' => 'AgentOrderController@checkDataBeforeCreateOrder',
                'permission' => addAllPermissionAgent(['orders.create']),
            ]);

            Route::get('orders/{order}/generate', [
                'as' => 'invoice.generate',
                'uses' => 'AgentOrderController@generateInvoice',
                'permission' => addAllPermissionAgent(['agent.orders.edit']),
            ])->wherePrimaryKey('order');
            Route::post('create-customer-when-creating-order', [
                'as' => 'customers.create-customer-when-creating-order',
                'uses' => 'AgentOrderController@postCreateCustomerWhenCreatingOrder',
                'permission' => addAllPermissionAgent(['agent.orders.create.store']),
            ]);

            Route::post('check-product-order-by-agent', [
                'as' => 'check-product-order-by-agent',
                'uses' => 'AgentOrderController@checkProductOrderInAgent',
                'permission' => addAllPermissionAgent(['agent.orders.create']),
            ]);

            Route::post('submit-payment', [
                'as' => 'submit-payment',
                'uses' => 'AgentOrderController@submitPayment',
                'permission' => addAllPermissionAgent(['agent.orders.create']),
            ]);
        });
    });
});

