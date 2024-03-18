<?php

namespace Botble\Showroom\Services;

use Botble\ACL\Models\User;
use Botble\Agent\Models\AgentUser;
use Botble\Showroom\Models\ShowroomUser;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Botble\Showroom\Services\Abstracts\StoreUserShowroomServiceAbstract;
use Illuminate\Http\Request;

class StorageUserShowroomService extends StoreUserShowroomServiceAbstract
{
    public function execute(Request $request, User $user): void
    {
        ShowroomUser::query()->where('user_id', $user->id)->delete();
        $showRooms = $request->input('showRoom_id', []);
        foreach ($showRooms as $showRoom) {
            $dataInsert = [
                'user_id' => $user->id,
                'showroom_id' => $showRoom
            ];
            ShowroomUser::query()->create($dataInsert);
        }
    }
    public function destroy(Request $request, User $user)
    {
        ShowroomUser::query()->where('user_id', $user->id)->delete();
    }
}
