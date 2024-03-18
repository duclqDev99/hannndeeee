<?php

namespace Botble\Agent\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentActualReceiptDetail extends BaseModel
{
    protected $table = 'agent_actual_receipt_detail';
    protected $fillable = [
        'actual_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
        'reason',
        'qrcode_id',
        'batch_id',
        'color',
        'size'
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
}
