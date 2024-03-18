<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceiptDetail extends BaseModel
{
    protected $table = 'hb_actual_receipt_detail';
    protected $fillable = [
        'actual_id',
        'product_id',
        'product_name',
        'price',
        'sku',
        'quantity',
        'reasoon',
    ];

    protected $casts = [
        'reasoon' => SafeContent::class,
    ];

    public $timestamps = false;




    public function product()
    {
        return $this->hasOne(Product::class,'id', 'product_id');
    }
}
