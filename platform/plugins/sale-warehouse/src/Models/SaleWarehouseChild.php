<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleWarehouseChild extends BaseModel
{
    protected $table = 'sw_sale_warehouse_children';

    protected $fillable = [
        'sale_warehouse_id',
        'name',
        'phone',
        'address',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => SaleWarehouseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function saleWarehouse()
    {
        return $this->belongsTo(SaleWarehouse::class, 'sale_warehouse_id','id');
    }
}
