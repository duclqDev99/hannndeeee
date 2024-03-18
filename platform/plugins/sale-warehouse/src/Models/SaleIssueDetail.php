<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleIssueDetail extends BaseModel
{
    protected $table = 'sw_sale_issue_detail';
    protected $fillable = [
        'sale_isue_id',
        'product_id',
        'quantity',
        'quantity_scan',
    ];
    public function product(){
        return $this->hasOne(Product::class, 'id','product_id');
    }
}
