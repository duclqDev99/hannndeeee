<?php

namespace Botble\Warehouse\Repositories\Interfaces;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Models\TypeMaterial;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TypeMaterialInterface extends RepositoryInterface
{
    // In TypeMaterialInterface
    public function getAllMaterialsWithChildren(array $condition = [], array $with = [], array $select = ['*']): Collection;
    public function getTypeMaterial(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection;
}
