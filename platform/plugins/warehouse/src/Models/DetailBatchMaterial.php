<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class DetailBatchMaterial extends BaseModel
{
    protected $table = 'wh_detail_batch_material';

    protected $fillable = [
        'reason',
        'quantity_actual',
        'quantity',
        'batch_code',
        'actual_out_detail_id',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];



    public $timestamps = false;



}
