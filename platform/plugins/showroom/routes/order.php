<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Showroom\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'showroom-orders', 'as' => 'showroom.orders.'], function () {
            Route::resource('', 'ShowroomOrderController')->parameters(['' => 'order']);

            Route::post('add-qr', [
               'as' => 'add-qr',
               'uses' => 'ShowroomOrderController@addQr',
               'permissions' => false
            ]);

            Route::post('confirm-qrcode-payment/{order}', [
                'as' => 'confirm-qrcode-payment',
                'uses' => 'ShowroomOrderController@postConfirmQrcodePayment',
                'permission' => true,
            ]);

            // Route::get('reorder', [
            //     'as' => 'reorder',
            //     'uses' => 'ShowroomOrderController@getReorder',
            //     'permission' => addAllPermissionShowroom([]), 'orders.create',
            // ]);

            Route::get('generate-invoice/{order}', [
                'as' => 'generate-invoice',
                'uses' => 'ShowroomOrderController@getGenerateInvoice',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('confirm', [
                'as' => 'confirm',
                'uses' => 'ShowroomOrderController@postConfirm',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ]);

            Route::post('send-order-confirmation-email/{order}', [
                'as' => 'send-order-confirmation-email',
                'uses' => 'ShowroomOrderController@postResendOrderConfirmationEmail',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('create-shipment/{order}', [
                'as' => 'create-shipment',
                'uses' => 'ShowroomOrderController@postCreateShipment',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('cancel-shipment/{shipment}', [
                'as' => 'cancel-shipment',
                'uses' => 'ShowroomOrderController@postCancelShipment',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('update-shipping-address/{address}', [
                'as' => 'update-shipping-address',
                'uses' => 'ShowroomOrderController@postUpdateShippingAddress',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('update-tax-information/{taxInformation}', [
                'as' => 'update-tax-information',
                'uses' => 'ShowroomOrderController@postUpdateTaxInformation',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::get('cancel-order/{order}', [
                'as' => 'cancel',
                'uses' => 'ShowroomOrderController@postCancelOrder',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('cancel-order/{order}', [
                'as' => 'cancel',
                'uses' => 'ShowroomOrderController@postCancelOrder',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::get('print-shipping-order/{order}', [
                'as' => 'print-shipping-order',
                'uses' => 'ShowroomOrderController@getPrintShippingOrder',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('confirm-payment/{order}', [
                'as' => 'confirm-payment',
                'uses' => 'ShowroomOrderController@postConfirmPayment',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::get('get-shipment-form/{order}', [
                'as' => 'get-shipment-form',
                'uses' => 'ShowroomOrderController@getShipmentForm',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::post('refund/{order}', [
                'as' => 'refund',
                'uses' => 'ShowroomOrderController@postRefund',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey();

            Route::get('get-available-shipping-methods', [
                'as' => 'get-available-shipping-methods',
                'uses' => 'ShowroomOrderController@getAvailableShippingMethods',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ]);

            Route::post('coupon/apply', [
                'as' => 'apply-coupon-when-creating-order',
                'uses' => 'ShowroomOrderController@postApplyCoupon',
                'permission' => addAllPermissionShowroom(['orders.create']),
            ]);

            Route::post('check-data-before-create-order', [
                'as' => 'check-data-before-create-order',
                'uses' => 'ShowroomOrderController@checkDataBeforeCreateOrder',
                'permission' => addAllPermissionShowroom(['showroom.orders.create']),
            ]);

            Route::get('orders/{order}/generate', [
                'as' => 'invoice.generate',
                'uses' => 'ShowroomOrderController@generateInvoice',
                'permission' => addAllPermissionShowroom(['showroom.orders.edit']),
            ])->wherePrimaryKey('order');
            Route::post('create-customer-when-creating-order', [
                'as' => 'customers.create-customer-when-creating-order',
                'uses' => 'ShowroomOrderController@postCreateCustomerWhenCreatingOrder',
                'permission' => addAllPermissionShowroom(['showroom.orders.create.store']),
            ]);

            Route::get('checkout', [
                'as' => 'checkout-payment',
                'uses' => 'ShowroomOrderController@viewCheckoutPayment',
                'permission' => 'showroom.orders.checkout-payment'
            ]);

            Route::get('print-showroom-order/{id}', [
                'as' => 'print-showroom-order',
                'uses' => 'ShowroomOrderController@printShowroomOrder',
                'permission' => 'showroom.orders.*'
            ]);

            Route::get('print-showroom-order-vat/{id}', [
                'as' => 'print-showroom-order-vat',
                'uses' => 'ShowroomOrderController@printShowroomOrderVAT',
                'permission' => 'showroom.orders.*'
            ]);

            Route::get('get-all-products-and-variations', [
                'as' => 'get-all-products-and-variations',
                'uses' => 'ShowroomOrderController@getAllProductAndVariations',
                'permission' => true,
            ]);
            Route::get('get-qrcode-in-add-search-product', [
                'as' => 'get-qrcode-in-add-search-product',
                'uses' => 'ShowroomOrderController@getQrcodeInAddSearchProduct',
                'permission' => true,
            ]);
            Route::post('confirm-bank-transfer', [
                'as' => 'confirm-bank-transfer',
                'uses' => 'ShowroomOrderController@postConfirmBankTransfer',
                'permission' => 'showroom.orders.edit',
            ]);
        });
    });
});

