<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AngentProposalIssueDetail extends BaseModel
{
    protected $table = 'agent_proposal_issue_detail';

    protected $fillable = [
        'proposal_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'size',
        'color',
        'quantity_submit'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}
