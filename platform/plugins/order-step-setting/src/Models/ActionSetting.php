<?php

namespace Botble\OrderStepSetting\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Sales\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActionSetting extends BaseModel
{
    protected $table = 'hd_action_setting';    
    protected $fillable = [
        'title',
        'action_code',
        'step_index',
        'department_code',
        'valid_status',
        'update_relate_actions',
        'action_type',
        'is_show',
        'action_type'
    ];
    protected $casts = [
        'title' => SafeContent::class,
        'department_code' => SafeContent::class,
        'update_relate_actions' => 'json'
    ];
}
