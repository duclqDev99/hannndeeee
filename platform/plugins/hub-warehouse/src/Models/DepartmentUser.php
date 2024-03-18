<?php

namespace Botble\HubWarehouse\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class DepartmentUser extends BaseModel
{
    protected $table = 'department_user';

    protected $fillable = [
        'department_code',
        'user_id'
    ];

    protected $casts = [
        'status' => HubStatusEnum::class,
        'name' => SafeContent::class,
    ];

}
