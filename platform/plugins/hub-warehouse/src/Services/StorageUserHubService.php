<?php

namespace Botble\HubWarehouse\Services;

use Botble\ACL\Models\User;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Services\Abstracts\StoreUserHubWarehouseServiceAbstract;
use Botble\Warehouse\Models\Material;
use Illuminate\Http\Request;

class StorageUserHubService extends StoreUserHubWarehouseServiceAbstract
{
    public function execute(Request $request, User $user): void
    {
        HubUser::query()->where('user_id', $user->id)->delete();
        $hubIds = $request->input('hub_id', []);
        foreach ($hubIds as $hubId) {
            $dataInsert = [
                'user_id' => $user->id,
                'hub_id' => $hubId
            ];
            HubUser::query()->create($dataInsert);
        }
    }
    public function destroy(Request $request, User $user)
    {
        HubUser::query()->where('user_id', $user->id)->delete();
    }
}
