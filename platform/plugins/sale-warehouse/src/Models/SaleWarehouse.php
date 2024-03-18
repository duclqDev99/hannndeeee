<?php

namespace Botble\SaleWarehouse\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleWarehouse extends BaseModel
{
    protected $table = 'sw_sale_warehouses';

    protected $fillable = [
        'hub_id',
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

    public function warehouseChild()
    {
        return $this->hasMany(SaleWarehouseChild::class, 'sale_warehouse_id', 'id');
    }
    public function saleUsers()
    {
        return $this->belongsToMany(
            User::class,
            'sw_user',
            'sale_warehouse_id',
            'user_id'
        );
    }
    public function hub(){
        return $this->belongsTo(HubWarehouse::class, 'hub_id');
    }
}
