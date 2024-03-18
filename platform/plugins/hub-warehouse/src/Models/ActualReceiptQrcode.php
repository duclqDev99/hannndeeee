<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceiptQrcode extends BaseModel
{
    protected $table = 'hb_actual_receipt_qrcode';
    protected $fillable = [
        'receipt_id',
        'product_id',
        'qrcode_id',
        'batch_id',
    ];

    public function receipt()
    {
        return $this->hasOne(HubReceipt::class, 'id','receipt_id');
    }

    public function productQrcode()
    {
        return $this->hasOne(ProductQrcode::class, 'id', 'qrcode_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function batch()
    {
        return $this->hasOne(ProductBatch::class, 'id', 'batch_id');
    }
}
