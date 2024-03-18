<?php

namespace Botble\HubWarehouse\Repositories\Interfaces;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Models\TypeMaterial;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserAgentInterface extends RepositoryInterface
{
    // In TypeMaterialInterface
    public function getAllUserAgent(array $condition = [], array $with = [], array $select = ['*']): Collection;
    public function getAgent(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection;
}
