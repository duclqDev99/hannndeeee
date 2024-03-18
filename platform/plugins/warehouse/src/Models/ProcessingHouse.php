<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Enums\StockStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProcessingHouse extends BaseModel
{
    protected $table = 'wh_processing_houses';

    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => StockStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function materialOuts()
    {
        return $this->morphMany(MaterialOut::class, 'warehouse');
    }
}
