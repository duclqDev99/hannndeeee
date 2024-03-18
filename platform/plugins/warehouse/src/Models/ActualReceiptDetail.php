<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceiptDetail extends BaseModel
{
    protected $table = 'wh_actual_receipt_details';

    protected $fillable = [
        'actual_id',
        'material_id',
        'material_code',
        'material_name',
        'material_unit',
        'material_quantity',
        'material_price',
        'reasoon',
    ];
    public $timestamps = false;

    protected $casts = [
        // 'material_code' => SafeContent::class,
        // 'material_unit' => SafeContent::class,
        // 'reasoon' => SafeContent::class,
    ];

    public function material($id)
    {
        if($id !== 0)
        {
            return $this->belongsTo(Material::class,'material_id', 'id');
        }
    }
}
