<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class TypeMaterial extends BaseModel
{
    protected $table = 'wh_type_materials';

    protected $fillable = [
        'name',
        'parent_id',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'material_typematerial')->with('slugable');
    }
}
