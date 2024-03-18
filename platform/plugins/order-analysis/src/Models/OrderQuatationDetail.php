<?php

namespace Botble\OrderAnalysis\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\Sales\Models\Order;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderQuatationDetail extends BaseModel
{
    protected $table = 'hd_order_quotation_detail';

    protected $fillable = [
        'quotation_id',
        'analysis_detail_id',
        'price',
    ];
}
