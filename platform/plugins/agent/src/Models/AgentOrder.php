<?php

namespace Botble\Agent\Models;

use Botble\Agent\Enums\OrderStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentOrder extends BaseModel
{
    protected $table = 'agent_orders';

    protected $fillable = [
        'code',
        'status',
        'description',
        'amount',
        'list_id_product_qrcode',
        'created_at',
        'where_type',
        'where_id',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'list_id_product_qrcode' => 'json',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function where()
    {
        return $this->morphTo();
    }
}
