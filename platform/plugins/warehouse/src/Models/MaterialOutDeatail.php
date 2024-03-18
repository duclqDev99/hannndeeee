<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialOutDeatail extends BaseModel
{
    protected $table = 'wh_material_proposal_out_details';

    protected $fillable = [
        'proposal_id',
        'material_code',
        'material_name',
        'material_unit',
        'material_quantity',
        'material_price',

    ];
    public $timestamps = false;


     public function batchMaterials(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialBatch::class,
            MaterialOutDeatail::class,
            'proposal_id',
            'material_code',
            'id',
            'material_code'
        )->orderBy('created_at')->where('quantity','>','0');
    }
    public function material($code)
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }
    public function materials()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }
    public function materialOut(): BelongsTo
    {
        return $this->belongsTo(MaterialOut::class,'proposal_id', 'id');
    }

}
