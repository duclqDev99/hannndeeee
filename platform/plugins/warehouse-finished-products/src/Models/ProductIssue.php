<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProductIssue extends BaseModel
{
    protected $table = 'wfp_product_issue';
    protected $fillable = [
        'proposal_id',
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
        'issue_code',
        'reason',
        'from_proposal_receipt',
        'image',
        'is_batch'

    ];

    protected $casts = [
        'status' => ProductIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function proposal()
    {
        if ($this->from_proposal_receipt == 0) {
            return $this->hasOne(ProposalProductIssue::class, 'id', 'proposal_id');
        }
        return $this->hasOne(ProposalReceiptProducts::class, 'id', 'proposal_id');
    }

    public function proposalHubReceipt()
    {
        return $this->hasOne(ProposalHubReceipt::class, 'id', 'proposal_id');
    }
    function productIssueDetail(): HasMany
    {

        return $this->hasMany(ProductIssueDetails::class, 'product_issue_id', 'id');

    }

    public function warehouse()
    {
        return $this->morphTo('warehouse', 'warehouse_type', 'warehouse_receipt_id');
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

    public function actualIssue()
    {
        return $this->hasOne(ActualIssue::class, 'product_issue_id', 'id');
    }
}
