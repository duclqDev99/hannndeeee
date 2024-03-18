<?php
namespace Botble\NotiAdminPusher\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Route;

class NotiAdminPusherController extends BaseController
{
    public function getAllPermissionByUser()
    {
        $user = auth()->user();
        if($user->isSuperUser()){
            return response()->json(array_flip(array_keys(Route::getRoutes()->getRoutesByName())));
        }
        return  response()->json($user->permissions);
    }
}
