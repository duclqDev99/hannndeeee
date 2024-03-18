<?php

namespace Botble\Warehouse\Repositories\Interfaces;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Models\Category;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryInterface extends RepositoryInterface
{
    public function getCategories(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection;

    public function getAllCategoriesWithChildren(array $condition = [], array $with = [], array $select = ['*']): Collection;
}
