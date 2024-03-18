<?php

namespace Botble\Sales\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Product extends BaseModel
{
    protected $table = 'hd_products';

    protected $fillable = [
        'name',
        'unit',
        'design_file_id',
        'sku',
        'price',
        'color',
        'size',
        'ingredient',
        'description',
        'image',
        'status',
    ];

    protected $casts = [
        'status' => SafeContent::class,
        'ingredient' => SafeContent::class,
        'description' => SafeContent::class,
        'color' => SafeContent::class,
        'size' => SafeContent::class,
        'sku' => SafeContent::class,
    ];
}
