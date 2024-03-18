<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentIssueDetail extends BaseModel
{
    protected $table = 'agent_issue_detail';
    protected $fillable = [
        'agent_issue_id',
        'product_id',
        'product_name',
        'sku',
        'color',
        'size',
        'price',
        'quantity',
    ];

    protected $casts = [
        'name' => SafeContent::class,
    ];
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
