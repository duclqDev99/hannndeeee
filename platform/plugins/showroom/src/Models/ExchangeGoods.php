<?php

namespace Botble\Showroom\Models;

use Botble\Base\Models\BaseModel;
use Botble\Showroom\Enums\ExchangeGoodsStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ExchangeGoods extends BaseModel
{
    protected $table = 'showroom_exchange_goods';

    protected $fillable = [
        'showroom_id',
        'total_quantity',
        'total_amount',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => ExchangeGoodsStatusEnum::class,
    ];

    public function exchangeDetail()
    {
        return $this->hasMany(ExchangeGoodsDetail::class, 'parent_id', 'id');
    }

    public function showroom()
    {
        return $this->hasOne(ShowroomWarehouse::class, 'id', 'showroom_id');
    }
}
