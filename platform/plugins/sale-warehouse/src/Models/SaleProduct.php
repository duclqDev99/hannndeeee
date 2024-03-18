<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleProduct extends BaseModel
{
    protected $table = 'sw_sale_product';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'quantity_sold',
        'quantity_issue',
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function warehouse()
    {
        return $this->belongsTo(SaleWarehouseChild::class, 'warehouse_id');

    }
}
