<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Botble\Warehouse\Enums\StockStatusEnum;
use Botble\Warehouse\Models\MaterialBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialWarehouse extends BaseModel
{
    protected $table = 'wh_warehouse';

    protected $fillable = [
        'name',
        'phone_number',
        'status',
        'address',
        'description',
    ];

    protected $casts = [
        'status' => StockStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'wh_quantity_material_stock', 'warehouse_id')->where('quantity', '>', '0')
            ->withPivot('quantity');
    }


    public function countBatchInStock($stock_id)
    {
        return MaterialBatch::where(['stock_id' => $stock_id])->count('stock_id');
    }

    public function totalMaterialInStock($stock_id)
    {
        return MaterialBatch::where(['stock_id' => $stock_id])
            ->whereHas('material', function ($query) {
                $query->where('status', MaterialStatusEnum::ACTIVE);
            })
            ->sum('quantity');
    }

    public function materialOuts()
    {
        return $this->morphMany(MaterialOut::class, 'warehouse');
    }
}
