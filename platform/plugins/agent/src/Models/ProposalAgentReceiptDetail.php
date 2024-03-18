<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\QuantityProductInStock;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalAgentReceiptDetail extends BaseModel
{
    protected $table = 'agent_proposal_receipt_detail';

    protected $fillable = [
        'id',
        'proposal_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
    ];
    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
    public function productHubStock()
    {
        return $this->belongsTo(QuantityProductInStock::class, 'product_id', 'product_id');
    }
}
