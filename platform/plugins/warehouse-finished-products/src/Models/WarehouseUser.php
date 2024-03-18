<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class WarehouseUser extends BaseModel
{
    protected $table = 'wfp_user_warehouse';

    protected $fillable = [
        'warehouse_id',
        'user_id'
    ];


    // public function warehus(){
    //     return $this->belongsTo(HubWarehouse::class,'hub_id', 'id');
    // }

}
