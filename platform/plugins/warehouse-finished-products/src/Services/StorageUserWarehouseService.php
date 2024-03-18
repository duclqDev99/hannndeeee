<?php

namespace Botble\WarehouseFinishedProducts\Services;

use Botble\ACL\Models\User;
use Botble\Agent\Models\AgentUser;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Botble\WarehouseFinishedProducts\Services\Abstracts\StoreUserWarehouseServiceAbstract;
use Illuminate\Http\Request;

class StorageUserWarehouseService extends StoreUserWarehouseServiceAbstract
{
    public function execute(Request $request, User $user): void
    {
        WarehouseUser::query()->where('user_id', $user->id)->delete();
        $warehouseIds = $request->input('warehouse_id', []);
        foreach ($warehouseIds as $warehouseId) {
            $dataInsert = [
                'user_id' => $user->id,
                'warehouse_id' => $warehouseId
            ];
            WarehouseUser::query()->create($dataInsert);
        }
    }
    public function destroy(Request $request, User $user)
    {
        WarehouseUser::query()->where('user_id', $user->id)->delete();
    }
}
