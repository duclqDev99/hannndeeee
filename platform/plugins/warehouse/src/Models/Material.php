<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Enums\MaterialStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Material extends BaseModel
{
    protected $table = 'wh_materials';

    protected $fillable = [
        'name',
        'status',
        'unit',
        'code',
        'image',
        'price',
        'description',
        'min',
    ];

    protected $casts = [
        'status' => MaterialStatusEnum::class,
        'name' => SafeContent::class,
    ];
    public function type_materials(): BelongsToMany
    {
        return $this->belongsToMany(TypeMaterial::class, 'wh_material_type');
    }
    public function quantity_stock(): HasMany
    {
        return $this->hasMany(QuantityMaterialStock::class, 'material_id', 'id');
    }
    public function materialBatches($stockId): HasMany
    {
        return $this->hasMany(MaterialBatch::class, 'material_id', 'id')->where('stock_id', $stockId);
    }
    public function warehouse()
    {
        return $this->belongsToMany(MaterialWarehouse::class, 'wh_quantity_material_stock', 'material_id')
            ->withPivot('quantity');
    }
}
