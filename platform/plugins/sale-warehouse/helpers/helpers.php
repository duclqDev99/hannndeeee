<?php

use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Models\SaleUser;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Carbon\Carbon;

if(!function_exists('addAllPermissionSaleWarehouse')){
    function addAllPermissionSaleWarehouse(array $permissions): array {
        if (!in_array('sale-warehouse.all', $permissions)) {
            $permissions[] = 'sale-warehouse.all';
        }
        return $permissions;
    }
}
if(!function_exists('get_sale_warehouse_for_user')){
    function get_sale_warehouse_for_user(){
        if(request()->user()->isSuperUser() || Auth::guard()->user()->hasPermission('sale-warehouse.all')){
            return \Botble\SaleWarehouse\Models\SaleWarehouse::query()->where('status', SaleWarehouseStatusEnum::ACTIVE)->get();
        }
        $user = request()->user();
        return $user->hub;
    }
}


if(!function_exists('get_list_sale_warehouse_id_for_current_user')){
    function get_list_sale_warehouse_id_for_current_user()
    {
        $userId = \Auth::user()->id;

        $saleUsers = SaleUser::query()->where('user_id', $userId)->get();

        $warehouseIds = collect();

        foreach ($saleUsers as $saleUser) {
            if($saleUser->saleWarehouse && $saleUser->saleWarehouse->warehouseChild) {
                $warehouseIds = $warehouseIds->merge($saleUser->saleWarehouse->warehouseChild->pluck('id'));
            }
        }

        if($warehouseIds->isNotEmpty()) {
            return $warehouseIds->toArray();
        }

        return [];
    }
}

/**
 * @param int|string $id: ID warehouse of the receipt/issue - proposal receipt/issue of hub
 * @return bool
 */
if(!function_exists('check_user_depent_of_sale_warehouse')){
    function check_user_depent_of_sale_warehouse($id){
        $user = \Auth::user();
        $listHub = SaleUser::query()->where(['user_id' => $user->id])->pluck('sale_warehouse_id')->all();

        $check = true;

        if($user->isSuperUser()){
            $check = false;
        }

        if(!empty($listHub)){
            if(in_array($id, $listHub))
            {
                $check = false;
            }
        }
        return $check;
    }
}
if(!function_exists('check_sale_eligibility')){
    function check_sale_eligibility($id){

        $productQrcode = ProductQrcode::find($id);
        $productionDate = Carbon::parse($productQrcode?->production_time);
        $now = Carbon::now();
        $monthsDifference = $productionDate->diffInMonths($now);
        $discountPercentage = 0;
        if ($monthsDifference > 12) {
            return true;
        }
        return false;
    }
}

if(!function_exists('get_price_sales_in_product')){
    function get_price_sales_in_product($id){

        $productQrcode = ProductQrcode::find($id);
        if($productQrcode?->warehouse_type != SaleWarehouseChild::class){
            return [];
        }
        $price = $productQrcode?->reference?->price;

        $productionDate = Carbon::parse($productQrcode?->production_time);
        $now = Carbon::now();
        $monthsDifference = $productionDate->diffInMonths($now);
        $discountPercentage = 0;
        if ($monthsDifference > 18) {
            $discountPercentage = 70;
        } elseif ($monthsDifference > 15) {
            $discountPercentage = 60;
        } elseif ($monthsDifference > 12) {
            $discountPercentage = 50;
        }

        $salePrice = $price - ($price * ($discountPercentage / 100));

        return $salePrice;
    }
}
