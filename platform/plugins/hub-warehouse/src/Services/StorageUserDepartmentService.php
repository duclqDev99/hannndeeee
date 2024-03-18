<?php

namespace Botble\HubWarehouse\Services;

use Botble\ACL\Models\User;
use Botble\HubWarehouse\Models\DepartmentUser;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Services\Abstracts\StoreUserHubWarehouseServiceAbstract;
use Botble\Warehouse\Models\Material;
use Illuminate\Http\Request;

class StorageUserDepartmentService extends StoreUserHubWarehouseServiceAbstract
{
    public function execute(Request $request, User $user): void
    {
        DepartmentUser::query()->where('user_id', $user->id)->delete();
        $departmentCodes = $request->input('department_id');
        foreach ($departmentCodes as $departmentCode) {
            $dataInsert = [
                'user_id' => $user->id,
                'department_code' => $departmentCode[0]
            ];
            DepartmentUser::query()->create($dataInsert);
        }
    }
    public function destroy(Request $request, User $user)
    {
        DepartmentUser::query()->where('user_id', $user->id)->delete();
    }
}
