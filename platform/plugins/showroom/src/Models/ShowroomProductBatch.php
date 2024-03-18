<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomProductBatch extends BaseModel
{
    protected $table = 'showroom_product_batchs';
    protected $fillable = [
        'receipt_id',
        'qrcode',
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
        'status' => BaseStatusEnum::class,
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
        return $this->belongsTo(ShowroomWarehouse::class, 'warehouse_id', 'id');
    }

    public function productInBatch()
    {
        return $this->hasMany(ShowroomProductBatchDetail::class, 'batch_id', 'id');
    }

    public function getQRCode()
    {
        return $this->hasOne(ShowroomProductBatchQrCode::class, 'batch_id', 'id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse', 'warehouse_type', 'warehouse_id');
    }
    public function listProduct()
    {
        return $this->hasMany(ShowroomProductBatchDetail::class, 'batch_id', 'id');
    }
    public function productDetails()
    {
        return $this->belongsToMany(ShowroomProductBatchDetail::class, 'batch_id', 'id');
    }
}
