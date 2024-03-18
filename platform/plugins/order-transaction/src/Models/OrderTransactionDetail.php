<?php

namespace Botble\OrderTransaction\Models;
use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderTransaction extends BaseModel
{

    protected $table = 'order_transaction_details';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'amount',
    ];
}
