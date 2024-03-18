<?php

namespace Botble\Showroom\Models;

use App\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Showroom\Models\ShowroomProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomWarehouse extends BaseModel
{
    protected $table = 'showroom_warehouse';

    protected $fillable = [
        'name',
        'showroom_id',
        'description',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => ShowroomStatusEnum::class,
        'name' => SafeContent::class,
        'address' => SafeContent::class,
        'description' => SafeContent::class,
    ];
    public function showroom(){
        return $this->belongsTo(Showroom::class,'showroom_id');
    }
    public function showroomProduct()
    {
        return $this->hasMany(ShowroomProduct::class, 'warehouse_id');

    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'wfp_product_in_stock', 'stock_id')
            ->withPivot('quantity');
    }
    public function totalProductInStock($stock_id)
    {
        return ShowroomProductBatch::where(['warehouse_id' => $stock_id, 'warehouse_type' => ShowroomWarehouse::class])->sum('quantity');
    }

}
