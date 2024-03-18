<?php

namespace Botble\WarehouseFinishedProducts\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;

use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Botble\WarehouseFinishedProducts\Repositories\Interfaces\UserWarehouseInterface;
use Illuminate\Database\Eloquent\Collection;

class UserWarehouseRepository extends RepositoriesAbstract implements UserWarehouseInterface
{
    public function getWarehouse(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection
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
    public function getAllUserWarehouse(array $condition = [], array $with = [], array $select = ['*']): Collection
    {
        $data = $this->model
            ->where($condition)
            ->with($with)
            ->select($select);

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
