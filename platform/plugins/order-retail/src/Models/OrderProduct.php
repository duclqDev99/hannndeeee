<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderProduct extends BaseModel
{
    protected $table = 'retail_order_products';
 
    protected $fillable = [
        'sku',
        'cal',
        'product_name',
        'ingredient',
        'price',
        'description',
        'options',
        'weight',
        'address',
        'shipping_method',
        'product_id',
        'order_id',
        'hgf_price',
        'quotation_price',
        'link_design'
    ];

    protected $casts = [
        'address' => SafeContent::class,
        'product_name' => SafeContent::class,
        'sku' => SafeContent::class,
    ];

    protected static function booted(): void
    {
        self::deleted(function (OrderProduct $product) {
            $product->fileDesign()->delete();
            $product->images()->each(fn (ProductImageFile $image) => $image->delete());
            $product->sizes()->each(fn (ProductSize $size) => $size->delete());
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function fileDesign(): HasOne
    {
        return $this->hasOne(ProductDesignFile::class, 'retail_product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImageFile::class, 'retail_product_id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class, 'retail_product_id');
    }
}
