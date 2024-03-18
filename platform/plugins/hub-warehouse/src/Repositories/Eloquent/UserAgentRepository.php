<?php

namespace Botble\HubWarehouse\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;

use Botble\HubWarehouse\Repositories\Interfaces\UserAgentInterface;
use Botble\Warehouse\Repositories\Interfaces\TypeMaterialInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class UserAgentRepository extends RepositoriesAbstract implements UserAgentInterface
{


    public function getAgent(array $select, array $orderBy, array $conditions = ['status' => BaseStatusEnum::PUBLISHED]): Collection
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
    public function getAllUserAgent(array $condition = [], array $with = [], array $select = ['*']): Collection
    {
        $data = $this->model
            ->where($condition)
            ->with($with)
            ->select($select);

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
