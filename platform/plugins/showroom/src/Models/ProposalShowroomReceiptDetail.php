<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalShowroomReceiptDetail extends BaseModel
{
    protected $table = 'showroom_proposal_receipt_detail';

    protected $fillable = [
        'id',
        'proposal_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
        'quantity_submit',
        'batch_id'
    ];
    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
    public function batch(){
        return $this->belongsTo(ProductBatch::class,'batch_id');
    }
    public function productHubStock()
    {
        return $this->belongsTo(QuantityProductInStock::class, 'product_id', 'product_id');
    }
}
