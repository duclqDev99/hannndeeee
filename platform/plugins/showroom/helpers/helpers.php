<?php

use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Showroom\Models\ShowroomUser;

if(!function_exists('get_showroom_for_user')){
    function get_showroom_for_user(){
        if(request()->user()->isSuperUser() || Auth::guard()->user()->hasPermission('showroom.all')){
            return \Botble\Showroom\Models\Showroom::query()->wherePublished()->get();
        }
        $user = request()->user();
        return $user->showroom;
    }
}

if(!function_exists('addAllPermissionShowroom')){
    function addAllPermissionShowroom(array $permissions): array {
        if (!in_array('showroom.all', $permissions)) {
            $permissions[] = 'showroom.all';
        }
        return $permissions;
    }
}

if(!function_exists('get_list_showroom_id_for_current_user')){
    function get_list_showroom_id_for_current_user()
    {
        $userId = \Auth::user()->id;

        $showroomUser = ShowroomUser::query()->where(['user_id' => $userId])->pluck('showroom_id')->all();

        if(!empty($showroomUser)){
            return $showroomUser;
        }
        return null;
    }
}

/**
 * @param int|string $id: ID warehouse of the receipt/issue - proposal receipt/issue of showroom
 * @return bool
 */
if(!function_exists('check_user_depent_of_showroom')){
    function check_user_depent_of_showroom($id){
        $user = \Auth::user();
        $listShowroom = ShowroomUser::query()->where(['user_id' => $user->id])->pluck('showroom_id')->all();

        $check = true;

        if($user->isSuperUser()){
            $check = false;
        }

        if(!empty($listShowroom)){
            if(in_array($id, $listShowroom))
            {
                $check = false;
            }
        }
        return $check;
    }
}