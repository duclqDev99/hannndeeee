<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomProposalIssueDetail extends BaseModel
{
    protected $table = 'showroom_proposal_issue_detail';

    protected $fillable = [
        'proposal_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'size',
        'color'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}
