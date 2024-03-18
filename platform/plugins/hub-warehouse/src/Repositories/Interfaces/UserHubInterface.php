<?php

namespace Botble\HubWarehouse\Repositories\Interfaces;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Models\TypeMaterial;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserHubInterface extends RepositoryInterface
{
    // In TypeMaterialInterface
    public function getAllUserHub(array $condition = [], array $with = [], array $select = ['*']): Collection;
    public function getHub(array $select, array $orderBy, array $conditions = ['status' => HubStatusEnum::ACTIVE]): Collection;
}
