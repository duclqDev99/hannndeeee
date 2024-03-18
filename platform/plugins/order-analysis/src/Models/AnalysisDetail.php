<?php

namespace Botble\OrderAnalysis\Models;

use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Models\Material;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class analysisDetail extends BaseModel
{
    protected $table = 'analysis_detail';

    protected $fillable = [
        'quantity',
        'analysis_material_id',
        'analysis_order_id',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'analysis_material_id', 'id');
    }
}
