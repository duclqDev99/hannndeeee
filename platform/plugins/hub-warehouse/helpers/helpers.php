<?php

use Botble\HubWarehouse\Models\HubUser;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

if(!function_exists('addAllPermissionHub')){
    function addAllPermissionHub(array $permissions): array {
        if (!in_array('hub-warehouse.all-permissions', $permissions)) {
            $permissions[] = 'hub-warehouse.all-permissions';
        }
        return $permissions;
    }
}
if(!function_exists('get_hub_for_user')){
    function get_hub_for_user(){
        if(request()->user()->isSuperUser() || Auth::guard()->user()->hasPermission('hub-warehouse.all-permissions')){
            return \Botble\HubWarehouse\Models\HubWarehouse::query()->where('status', HubStatusEnum::ACTIVE)->get();
        }
        $user = request()->user();
        return $user->hub;
    }
}


if(!function_exists('get_list_hub_id_for_current_user')){
    function get_list_hub_id_for_current_user()
    {
        $userId = \Auth::user()->id;

        $hubUser = HubUser::query()->where(['user_id' => $userId])->pluck('hub_id')->all();

        if(!empty($hubUser)){
            return $hubUser;
        }
        return null;
    }
}


/**
 * @param int|string $id: ID warehouse of the receipt/issue - proposal receipt/issue of hub
 * @return bool
 */
if(!function_exists('check_user_depent_of_hub')){
    function check_user_depent_of_hub($id){
        $user = \Auth::user();
        $listHub = HubUser::query()->where(['user_id' => $user->id])->pluck('hub_id')->all();

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