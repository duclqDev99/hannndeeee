<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProductIssueDetails extends BaseModel
{
    protected $table = 'wfp_product_issue_detail';
    protected $fillable = [
        'product_issue_id',
        'product_id',
        'product_name',
        'isser_id',
        'sku',
        'color',
        'size',
        'price',
        'quantity',
        'is_batch'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function product($id)
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function productBatch()
    {
        return $this->hasMany(ProductBatchDetail::class, 'product_id', 'product_id');
    }

    public function actualIssues()
    {
        return $this->belongsTo(ActualIssue::class, 'id', 'product_issue_detail_id');
    }
}
