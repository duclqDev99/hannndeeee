<?php

namespace Botble\OrderTransaction\Models;

use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderTransaction extends BaseModel
{
    protected $table = 'order_transactions';

    protected $fillable = [
        'order_id',
        'transaction_code',
        'created_by',
        'status',
        'amount',
    ];
}
