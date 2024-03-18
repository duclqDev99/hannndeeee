<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleReceiptDetail extends BaseModel
{
    protected $table = 'sw_sale_receipt_detail';

    protected $fillable = [
        'sale_receipt_id',
        'product_id',
        'product_name',
        'sku',
        'color',
        'size',
        'quantity',
        'batch_id',
        'qrcode_id'
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function batch(){
        return $this->hasOne(ProductBatch::class, 'id', 'batch_id');
    }
}
