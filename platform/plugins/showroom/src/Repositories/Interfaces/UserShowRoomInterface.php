<?php

namespace Botble\Showroom\Repositories\Interfaces;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface UserShowRoomInterface extends RepositoryInterface
{
    // In TypeMaterialInterface
    public function getAllUserShowRoom(array $condition = [], array $with = [], array $select = ['*']): Collection;
    public function getShowRoom(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection;
}
