<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProductBatch extends BaseModel
{
    protected $table = 'wfp_product_batchs';
    protected $fillable = [
        'receipt_id',
        'batch_code',
        'quantity',
        'start_qty',
        'status',
        'warehouse_id',
        'warehouse_type',
        'product_parent_id',
    ];


    protected $casts = [
        'warehouse_type' => SafeContent::class,
        'product_name' => SafeContent::class,
        'status' => ProductBatchStatusEnum::class,
    ];
    public function receipt()
    {
        return $this->belongsTo(ReceiptProduct::class, 'receipt_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_parent_id', 'id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(WarehouseFinishedProducts::class, 'warehouse_id', 'id');
    }

    public function productInBatch()
    {
        return $this->hasMany(ProductBatchDetail::class, 'batch_id', 'id');
    }

    public function getQRCode()
    {
        return $this->hasOne(ProductQrcode::class, 'reference_id', 'id')->where('reference_type', '=', ProductBatch::class);
    }


    public function warehouse()
    {
        return $this->morphTo('warehouse', 'warehouse_type', 'warehouse_id');
    }
    public function listProduct()
    {
        return $this->hasMany(ProductBatchDetail::class, 'batch_id', 'id');
    }
    public function productDetails()
    {
        return $this->belongsToMany(ProductBatchDetail::class, 'batch_id', 'id');
    }
    public function batchQrCode()
    {
        return $this->hasOne(ProductQrcode::class, 'reference_id', 'id');
    }
}
