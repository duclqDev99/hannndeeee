<?php

namespace Botble\HubWarehouse\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class HubWarehouse extends BaseModel
{
    protected $table = 'hb_hubs';

    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => HubStatusEnum::class,
        'name' => SafeContent::class,
    ];
    public function hubUsers()
    {
        return $this->belongsToMany(
            User::class,
            'hb_user_hub',
            'hub_id',
            'user_id'
        );
    }
    public function warehouseInHub()
    {
        return $this->hasMany(Warehouse::class, 'hub_id', 'id')->where('is_watse', 0);
    }
    public function warehouseWatseInHub()
    {
        return $this->hasMany(Warehouse::class, 'hub_id', 'id')->where('is_watse', 1);
    }

    public function saleWarehouse()
    {

        return $this->hasOne(SaleWarehouse::class, 'hub_id', 'id');
    }
}
