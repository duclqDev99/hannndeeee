<?php

namespace Botble\WarehouseFinishedProducts\Repositories\Interfaces;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface UserWarehouseInterface extends RepositoryInterface
{
    // In TypeMaterialInterface
    public function getAllUserWarehouse(array $condition = [], array $with = [], array $select = ['*']): Collection;
    public function getWarehouse(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection;
}
