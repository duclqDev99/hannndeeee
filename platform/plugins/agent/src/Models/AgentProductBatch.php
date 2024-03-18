<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\Agent\Models\AgentProductBatchQrCode;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentProductBatch extends BaseModel
{
    protected $table = 'agent_product_batchs';
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
        return $this->belongsTo(AgentWarehouse::class, 'warehouse_id', 'id');
    }

    public function productInBatch()
    {
        return $this->hasMany(AgentProductBatchDetail::class, 'batch_id', 'id');
    }

    public function getQRCode()
    {
        return $this->hasOne(AgentProductBatchQrCode::class, 'batch_id', 'id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse', 'warehouse_type', 'warehouse_id');
    }
    public function listProduct()
    {
        return $this->hasMany(AgentProductBatchDetail::class, 'batch_id', 'id');
    }
    public function productDetails()
    {
        return $this->belongsToMany(AgentProductBatchDetail::class, 'batch_id', 'id');
    }
}
