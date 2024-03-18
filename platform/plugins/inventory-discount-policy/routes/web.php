<?php

use Botble\Base\Facades\BaseHelper;
use Botble\InventoryDiscountPolicy\Models\InventoryDiscountPolicy;
use Botble\Showroom\Models\Showroom;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\InventoryDiscountPolicy\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'inventory-discount-policies', 'as' => 'inventory-discount-policy.'], function () {
            Route::resource('', 'InventoryDiscountPolicyController')->parameters(['' => 'inventory-discount-policy']);

            Route::get('get-list-policy-showroom', [
                'as' => 'get-list-policy-showroom',
                'uses' => function(){
                    return InventoryDiscountPolicy::where(['type_warehouse' =>  Showroom::class, 'status' =>HubStatusEnum::ACTIVE() ])->pluck('name','id');
                },
                'permission' => false
            ]);
        });
    });

});
