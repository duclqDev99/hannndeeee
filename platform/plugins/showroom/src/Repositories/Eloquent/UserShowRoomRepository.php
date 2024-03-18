<?php

namespace Botble\Showroom\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;

use Botble\Showroom\Repositories\Interfaces\UserShowRoomInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class UserShowRoomRepository extends RepositoriesAbstract implements UserShowRoomInterface
{
    public function getShowRoom(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection
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
    public function getAllUserShowRoom(array $condition = [], array $with = [], array $select = ['*']): Collection
    {
        $data = $this->model
            ->where($condition)
            ->with($with)
            ->select($select);

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
