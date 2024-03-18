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
class SaleProposalIssueDetail extends BaseModel
{
    protected $table = 'sw_sale_proposal_issue_details';


    protected $fillable = [
        'proposal_id',
        'product_id',
        'quantity',

    ];
    protected $casts = [
    ];
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }


    public function proposal()
    {
        return $this->belongsTo(SaleWarehouseChild::class, 'proposal_id');
    }
    public function productStock()
    {
        return $this->belongsTo(SaleProduct::class, 'product_id', 'product_id')
           ;
    }
}
