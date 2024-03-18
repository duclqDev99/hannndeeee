<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalProductIssue extends BaseModel
{
    protected $table = 'wfp_proposal_product_issue';
    protected $fillable = [
        'warehouse_id',
        'warehouse_issue_type',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_receipt_id',
        'warehouse_type',
        'general_order_code',
        'is_warehouse',
        'quantity',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
        'proposal_code',
        'proposal_receipt_id',
        'reason',
        'is_odd',
        'is_batch',
        'expect_date_examine',
        'description_examine'

    ];

    protected $casts = [
        'status' => ProposalIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];

    function proposalProductIssueDetail(): HasMany
    {

        return $this->hasMany(ProposalProductIssueDetails::class, 'proposal_product_issue_id', 'id');

    }
    public function warehouse()
    {
        return $this->morphTo('warehouse', 'warehouse_type', 'warehouse_receipt_id');
    }
    public function proposalReceipt()
    {
        return $this->belongsTo(ProposalReceiptProducts::class, 'id', 'proposal_issue_id');
    }
    public function warehouseUsers()
    {
        return $this->belongsToMany(
            User::class,
            'wfp_user_warehouse',
            'warehouse_id',
            'user_id'
        );
    }
    public function warehouseQR()
    {
        return $this->belongsTo(WarehouseFinishedProducts::class, 'warehouse_id');
    }


}
