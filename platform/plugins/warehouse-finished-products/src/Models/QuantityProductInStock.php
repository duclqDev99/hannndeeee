<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Warehouse\Models\ProcessingHouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class QuantityProductInStock extends BaseModel
{
    protected $table = 'wfp_product_in_stock';
    protected $fillable = [
        'product_id',
        'stock_id',
        'quantity',
        'quantity_sold',
        'quantity_issue'
    ];

    public $timestamps = false;
    public function warehouse()
    {
        return $this->belongsTo(WarehouseFinishedProducts::class, 'stock_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

}
