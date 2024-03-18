<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\WarehouseFinishedProducts\Enums\BatchDetailStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProductBatchDetail extends BaseModel
{
    protected $table = 'wfp_product_batch_details';
    protected $fillable = [
        'batch_id',
        'product_id',
        'qrcode_id',
        'product_id',
        'product_name',
        'sku',
        'status',
    ];
    public $timestamps = false;

    protected $casts = [
        'status' => BatchDetailStatusEnum::class,
    ];

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id', 'id');
    }
    public function statusQrCode()
    {
        return $this->belongsTo(ProductQrcode::class, 'qrcode_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
