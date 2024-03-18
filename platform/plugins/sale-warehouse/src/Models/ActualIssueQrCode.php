<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;


/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualIssueQrCode extends BaseModel
{
    protected $table = 'sw_actual_issue_qrcode';
    protected $fillable = [
        'issue_id',
        'product_id',
        'qrcode_id',
        'batch_id',
        'is_batch',
        'qr'
    ];
    public $timestamps = false;
    protected $casts = [
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function qrcode()
    {
        return $this->hasOne(ProductQrcode::class, 'id', 'qrcode_id');
    }
    public function batch(){
        return $this->hasOne(ProductBatch::class, 'id', 'batch_id');
    }

}
