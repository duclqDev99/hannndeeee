<?php

namespace Botble\Sales\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\Sales\Models\Order;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderQuotation extends BaseModel
{
    protected $table = 'hd_order_quotation';

    protected $fillable = [
        'order_id',
        'title',
        'total_amount',
        'effective_time',
        'effective_payment',
        'transport_costs',
        'status',
        'is_canceled',
        'reason',
        'description',
    ];

    protected $casts = [
        'is_canceled' => SafeContent::class,
        'status' => OrderQuotationStatusEnum::class,
    ];
}
