<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Warehouse\Models\ProcessingHouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalReceiptProductDetails extends BaseModel
{
    protected $table = 'wfp_proposal_receipt_product_detail';
    protected $fillable = [
        'proposal_id',
        'product_id',
        'product_name',
        'processing_house_id',
        'processing_house_name',
        'price',
        'sku',
        'quantity',
        'color',
        'size',
    ];

    protected $casts = [
        'processing_house_name' => SafeContent::class,
        'color' => SafeContent::class,
        'size' => SafeContent::class,
    ];

    public $timestamps = false;

    public function processingHouse(): BelongsTo
    {
        return $this->belongsTo(ProcessingHouse::class,'processing_house_id', 'id');
    }


    public function product($id)
    {
        if($id !== 0)
        {
            return $this->belongsTo(Product::class,'product_id', 'id');
        }
    }
    public function prd()
    {
        return $this->belongsTo(Product::class,'product_id', 'id');
    }
}
