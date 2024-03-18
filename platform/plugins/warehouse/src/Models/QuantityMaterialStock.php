<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class QuantityMaterialStock extends BaseModel
{
    protected $table = 'wh_quantity_material_stock';

    protected $fillable = [
        'warehouse_id',
        'material_id',
        'quantity',
    ];
    public $timestamps = false;


    public function warehouse()
    {
        return $this->belongsTo(MaterialWarehouse::class, 'warehouse_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }


    public function inventoryMaterials()
    {
        return $this->belongsToMany(MaterialWarehouse::class, 'wh_quantity_material_stock', 'material_id', 'warehouse_id')
            ->withPivot('quantity');
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'wh_quantity_material_stock', 'warehouse_id', 'material_id')
            ->withPivot('quantity');
    }

}
