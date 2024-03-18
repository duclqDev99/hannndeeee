<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class QuantityProductInStock extends BaseModel
{
    protected $table = 'hb_product_in_stock';
    protected $fillable = [
        'product_id',
        'stock_id',
        'quantity',
        'quantity_sold',
        'quantity_issue'
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'stock_id');
    }

}
