<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceiptDetail extends BaseModel
{
    protected $table = 'wfp_actual_receipt_detail';
    protected $fillable = [
        'actual_id',
        'product_id',
        'product_name',
        'price',
        'sku',
        'quantity',
        'reasoon',
    ];

    protected $casts = [
        'processing_house_name' => SafeContent::class,
        'reasoon' => SafeContent::class,
    ];

    public $timestamps = false;
    
    public function processingHouse(): BelongsTo
    {
        return $this->belongsTo(ProcessingHouse::class,'processing_house_id', 'id');
    }


    public function product()
    {
        return $this->hasOne(Product::class,'id', 'product_id');
    }
}
