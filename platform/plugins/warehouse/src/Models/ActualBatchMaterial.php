<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualBatchMaterial extends BaseModel
{
    protected $table = 'wh_batch_material';

    protected $fillable = [
        'confirm_detail_id',
        'quantity',
        'reason',
        'material_code',
        'material_id'
    ];


    public function autualDetail()
    {
        return $this->hasMany(DetailBatchMaterial::class, 'actual_out_detail_id', 'id');
    }

}
