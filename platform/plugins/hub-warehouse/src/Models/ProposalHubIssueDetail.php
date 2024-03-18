<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalHubIssueDetail extends BaseModel
{
    protected $table = 'hb_proposal_hub_issue_detail';
    protected $fillable = [
        'proposal_id',
        'product_id',
        'product_name',
        'color',
        'size',
        'price',
        'sku',
        'quantity',
        'is_batch',
        'batch_id'
    ];

    protected $casts = [
        'name' => SafeContent::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function productBatch()
    {
        return $this->hasMany(ProductBatch::class, 'product_parent_id', 'product_id');
    }
    public function batch(){
        return $this->belongsTo(ProductBatch::class, 'batch_id', 'id');

    }
    public function productStock()
    {
        return $this->belongsTo(QuantityProductInStock::class, 'product_id', 'product_id');

    }


}
