<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ExchangeGoodsDetail extends BaseModel
{
    protected $table = 'showroom_exchange_goods_detail';

    protected $fillable = [
        'parent_id',
        'order_id',
        'qr_id_change',
        'price_product_change',
        'qr_id_pay',
        'price_product_pay',
        'option',
        'price_additional',
        'description',
    ];

    protected $casts = [
        'description' => SafeContent::class,
    ];
}
