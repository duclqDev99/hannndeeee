<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Enums\WarehouseEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class WarehouseFinishedProducts extends BaseModel
{
    protected $table = 'wfp_warehouse_finished_products';

    protected $fillable = [
        'name',
        'status',
        'description',
        'address',
        'phone_number',
    ];

    protected $casts = [
        'status' => WarehouseEnum::class,
        'name' => SafeContent::class,
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'wfp_product_in_stock', 'stock_id')
            ->withPivot('quantity');
    }
    public function totalProductInStock($stock_id)
    {
        return QuantityProductInStock::where(['stock_id' => $stock_id])->where('quantity', '>', 0);
    }

    public function warehouseUsers()
    {
        return $this->belongsToMany(
            User::class,
            'wfp_user_warehouse',
            'warehouse_id',
            'user_id'
        );
    }

}
