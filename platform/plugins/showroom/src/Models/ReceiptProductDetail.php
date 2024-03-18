<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ReceiptProductDetail extends BaseModel
{
    protected $table = 'wfp_receipt_products_detail';
    protected $fillable = [
        'receipt_id',
        'product_id',
        'product_name',
        'processing_house_id',
        'processing_house_name',
        'price',
        'sku',
        'quantity',
        'color',
        'size',
    ];

    protected $casts = [
        'processing_house_name' => SafeContent::class,
        'color' => SafeContent::class,
        'size' => SafeContent::class,
    ];

    public $timestamps = false;


    public function product()
    {
        return $this->hasOne(Product::class,'id', 'product_id');
    }
}
