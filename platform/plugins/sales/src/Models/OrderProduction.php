<?php

namespace Botble\Sales\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\Sales\Models\Order;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderProduction extends BaseModel
{
    protected $table = 'hd_order_productions';

    protected $fillable = [
        'code',
        'customer_name',
        'email',
        'phone',
        'effective_date',
        'pay_due_date',
        'is_paid',
        'order_id',
        'created_by_id',
    ];

    protected $casts = [
        'code' => SafeContent::class,
    ];


}
