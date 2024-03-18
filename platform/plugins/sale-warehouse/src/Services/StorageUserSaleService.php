<?php

namespace Botble\SaleWarehouse\Services;

use Botble\ACL\Models\User;
use Botble\SaleWarehouse\Models\SaleUser;
use Botble\SaleWarehouse\Services\Abstracts\StoreUserSaleServiceAbstract;
use Illuminate\Http\Request;

class StorageUserSaleService extends StoreUserSaleServiceAbstract
{
    public function execute(Request $request, User $user): void
    {
        SaleUser::query()->where('user_id', $user->id)->delete();
        $sales = $request->input('sale_warehouse_id', []);
        foreach ($sales as $sale) {
            $dataInsert = [
                'user_id' => $user->id,
                'sale_warehouse_id' => $sale
            ];
            SaleUser::query()->create($dataInsert);
        }
    }
    public function destroy(Request $request, User $user)
    {
        SaleUser::query()->where('user_id', $user->id)->delete();
    }
}
