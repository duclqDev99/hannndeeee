<?php

namespace Botble\HubWarehouse\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class HubUser extends BaseModel
{
    protected $table = 'hb_user_hub';

    protected $fillable = [
        'hub_id',
        'user_id'
    ];

    protected $casts = [
        'status' => HubStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function hub(){
        return $this->belongsTo(HubWarehouse::class,'hub_id', 'id');
    }

}
