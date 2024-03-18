<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualOutDetail extends BaseModel
{
    protected $table = 'wh_actual_out_details';

    protected $fillable = [
        'name',
        'actual_out_id',

        'material_code',
        'material_name',
        'material_unit',


        'material_price',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function material($id)
    {
        if($id !== 0)
        {
            return $this->belongsTo(Material::class,'material_id', 'id');
        }
    }
    public function detailBatchMaterial(): HasMany
    {
        return $this->hasMany(DetailBatchMaterial::class, 'actual_out_detail_id', 'id');
    }
}
