<?php

namespace Botble\Department\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Department\Enums\OrderDepartmentStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderDepartment extends BaseModel
{
    protected $table = 'order_departments';

    protected $fillable = [
        'order_id',
        'department_code',
        'assignee_id',
        'status',
        'expected_date',
        'completion_date'

    ];

    protected $casts = [
        'department_code' => SafeContent::class,
    ];



}
