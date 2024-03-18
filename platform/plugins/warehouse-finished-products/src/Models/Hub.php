<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Hub extends BaseModel
{
    protected $table = 'hb_hubs';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => HubStatusEnum::class,
        'name' => SafeContent::class,
    ];
}
