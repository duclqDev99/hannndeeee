<?php

namespace Botble\Agent\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentStatistics extends BaseModel
{
    protected $table = 'agent_statistics';

    protected $fillable = [
        'revenue',
        'quantity_product',
        'where_type',
        'where_id',
        'created_at',
        'updated_at',
    ];
}
