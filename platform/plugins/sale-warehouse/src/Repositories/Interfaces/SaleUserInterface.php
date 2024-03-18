<?php

namespace Botble\SaleWarehouse\Repositories\Interfaces;

use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface SaleUserInterface extends RepositoryInterface
{
    // In TypeMaterialInterface
    public function getAllSaleUser(array $condition = [], array $with = [], array $select = ['*']): Collection;
    public function getSaleWarehouse(array $select, array $orderBy, array $conditions = ['status' => SaleWarehouseStatusEnum::ACTIVE]): Collection;
}
