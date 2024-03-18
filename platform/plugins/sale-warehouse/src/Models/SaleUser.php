<?php

namespace Botble\SaleWarehouse\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleUser extends BaseModel
{
    protected $table = 'sw_user';

    protected $fillable = [
        'sale_warehouse_id',
        'user_id'
    ];

    public function saleWarehouse(){
        return $this->belongsTo(SaleWarehouse::class,'sale_warehouse_id', 'id');
    }

}
