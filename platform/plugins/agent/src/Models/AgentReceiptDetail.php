<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentReceiptDetail extends BaseModel
{
    protected $table = 'agent_receipt_detail';
    protected $fillable = [
        'agent_receipt_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
        'color',
        'size',
        'batch_id',
        'qrcode_id'
    ];

    protected $casts = [
        'processing_house_name' => SafeContent::class,
        'color' => SafeContent::class,
        'size' => SafeContent::class,
    ];

    public $timestamps = false;


    // public function processingHouse(): BelongsTo
    // {
    //     return $this->belongsTo(ProcessingHouse::class,'processing_house_id', 'id');
    // }


    public function product()
    {
        return $this->hasOne(Product::class,'id', 'product_id');
    }
    public function batch(){
        return $this->hasOne(ProductBatch::class,'id', 'batch_id');
    }
}
