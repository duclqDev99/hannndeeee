<?php

namespace Botble\Agent\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\HubWarehouse\Models\HubWarehouse;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Agent extends BaseModel
{
    protected $table = 'agents';

    protected $fillable = [
        'name',
        'phone_number',
        'description',
        'address',
        'status',
        'discount_value',
        'discount_type',
        'hub_id',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];
    public function agentOrders()
    {
        return $this->morphMany(AgentOrder::class, 'where');
    }
    public function orders()
    {
        return $this->morphMany(Order::class, 'where', 'where_type', 'where_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, AgentUser::class);
    }
    public function warehouseInAgent()
    {
        return $this->hasMany(AgentWarehouse::class, 'agent_id', 'id');
    }

    public function hub()
    {
        return $this->belongsTo(HubWarehouse::class, 'hub_id', 'id');

    }
}
