<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalHubReceiptDetail extends BaseModel
{
    protected $table = 'hb_proposal_hub_recepit_detail';

    protected $fillable = [
        'proposal_id',
        'product_id',
        'product_name',
        'sku',
        'price',
        'quantity',
        'is_batch',
        'color',
        'size',
        'batch_id',

    ];
    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function productStock()
    {
        return $this->belongsTo(QuantityProductInStock::class, 'product_id', 'product_id');
    }
}
