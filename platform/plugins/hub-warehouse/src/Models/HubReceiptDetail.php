<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class HubReceiptDetail extends BaseModel
{
    protected $table = 'hb_hub_receipt_detail';
    protected $fillable = [
        'hub_receipt_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
        'color',
        'size',
        'batch_id',
        'is_odd',
        'qrcode_id'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function batch()
    {
        return $this->hasOne(ProductBatch::class, 'id', 'batch_id');
    }
}
