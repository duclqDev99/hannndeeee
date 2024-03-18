<?php

namespace Botble\Showroom\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomProduct extends BaseModel
{
    protected $table = 'showroom_products';
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity_qrcode',
        'quantity_qrcode_sold',
        'quantity_not_qrcode',
        'quantity_sold_not_qrcode',
        'where_id',
        'where_type',
        'quantity_qrcode_issue'
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function warehouse()
    {
        return $this->belongsTo(ShowroomWarehouse::class, 'warehouse_id');
    }
    // protected $fillable = [
    //     'order_id',
    //     'where_type',
    //     'where_id',
    // ];

    // public function order()
    // {
    //     return $this->belongsTo(Order::class);
    // }

    // public function where()
    // {
    //     return $this->morphTo();
    // }
}
