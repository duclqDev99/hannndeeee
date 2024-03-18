<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Warehouse extends BaseModel
{
    protected $table = 'hb_warehouse_stock';

    protected $fillable = [
        'name',
        'hub_id',
        'status',
        'is_watse'
    ];

    protected $casts = [
        'status' => HubStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function hub()
    {
        return $this->belongsTo(HubWarehouse::class, 'hub_id');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'hb_product_in_stock', 'stock_id')
            ->withPivot('quantity');
    }
    public function quantityInstock()
    {
        return $this->hasMany(QuantityProductInStock::class, 'stock_id', 'id');
    }
}
