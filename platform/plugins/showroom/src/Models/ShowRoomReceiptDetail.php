<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowRoomReceiptDetail extends BaseModel
{
    protected $table = 'showroom_receipt_detail';
    protected $fillable = [
        'showroom_receipt_id',
        'product_id',
        'product_name',
        'sku',
        'color',
        'size',
        'price',
        'quantity',
        'batch_id',
        'qrcode_id'
    ];
    protected $casts = [
        'color' => SafeContent::class,
        'size' => SafeContent::class,
    ];

    public $timestamps = false;
    public function product()
    {
        return $this->hasOne(Product::class,'id', 'product_id');
    }
    public function batch(){
        return $this->hasOne(ProductBatch::class,'id', 'batch_id');
    }
}
