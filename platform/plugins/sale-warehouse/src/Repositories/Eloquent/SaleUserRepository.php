<?php

namespace Botble\SaleWarehouse\Repositories\Eloquent;

use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Repositories\Interfaces\SaleUserInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class SaleUserRepository extends RepositoriesAbstract implements SaleUserInterface
{


    public function getSaleWarehouse(array $select, array $orderBy, array $conditions = ['status' => SaleWarehouseStatusEnum::ACTIVE]): Collection
    {
        $data = $this->model
            ->select($select);
        if ($conditions) {
            $data = $data->where($conditions);
        }

        foreach ($orderBy as $by => $direction) {
            $data = $data->orderBy($by, $direction);
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }
    public function getAllSaleUser(array $condition = [], array $with = [], array $select = ['*']): Collection
    {
        $data = $this->model
            ->where($condition)
            ->with($with)
            ->select($select);

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
