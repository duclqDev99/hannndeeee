<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleActualReceiptDetail extends BaseModel
{
    protected $table = 'sw_sale_actual_receipt_detail';
    protected $fillable = [
        'actual_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
        'reason',
        'qrcode_id',
        'batch_id'
    ];
    public $timestamps = false;
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public function qrcode()
    {
        return $this->belongsTo(ProductQrcode::class, 'qrcode_id', 'id');
    }
    public function batch(){
        return $this->belongsTo(ProductBatch::class, 'batch_id', 'id');

    }
}
