<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class HubIssueDetail extends BaseModel
{
    protected $table = 'hb_hub_issue_detail';

    protected $fillable = [
        'hub_issue_id',
        'product_id',
        'product_name',
        'sku',
        'color',
        'size',
        'price',
        'quantity',
        'quantity_scan',
        'is_batch',
        'batch_id'
    ];

    protected $casts = [

    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function productBatch()
    {
        return $this->hasMany(ProductBatchDetail::class, 'product_id', 'product_id');
    }
    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
