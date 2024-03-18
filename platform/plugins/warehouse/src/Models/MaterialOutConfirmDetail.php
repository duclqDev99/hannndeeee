<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialOutConfirmDetail extends BaseModel
{
    protected $table = 'wh_material_out_confirm_details';

    protected $fillable = [
        'out_id',
        'material_code',
        'material_name',
        'material_unit',
        'material_quantity',
        'material_price',

    ];
    public $timestamps = false;

    public function material($code)
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }

    public function batch(): HasMany
    {
        return $this->hasMany(DetailBatchMaterial::class, 'actual_out_detail_id', 'id');
    }
    public function materialBatch()
    {
        return $this->hasMany(MaterialBatch::class, 'material_code', 'material_code')->where('quantity', '>', 0);
    }
    public function actualBatchMaterial(): BelongsTo
    {
        return $this->belongsTo(ActualBatchMaterial::class,'id','confirm_detail_id');
    }

}
