<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalProductIssueDetails extends BaseModel
{
    protected $table = 'wfp_proposal_product_issue_detail';
    protected $fillable = [
        'proposal_product_issue_id',
        'product_id',
        'product_name',
        'color',
        'size',
        'price',
        'sku',
        'quantity',
        'quantityExamine'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
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
    public function productStock()
    {
        return $this->belongsTo(QuantityProductInStock::class, 'product_id', 'product_id');

    }


}
