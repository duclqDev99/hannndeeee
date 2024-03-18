<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalPurchaseGoodsDetail extends BaseModel
{
    protected $table = 'wh_purchase_goods_details';

    protected $fillable = [
        'proposal_id',
        'supplier_id',
        'supplier_name',
        'material_code',
        'material_name',
        'material_unit',
        'material_quantity',
        'material_price',
        'material_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'supplier_name' => SafeContent::class,
        'material_code' => SafeContent::class,
        'material_name' => SafeContent::class,
        'material_unit' => SafeContent::class,
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class,'supplier_id', 'id');
    }


    public function material($id)
    {
        if($id !== 0)
        {
            return $this->belongsTo(Material::class,'material_id', 'id');
        }
    }

}
