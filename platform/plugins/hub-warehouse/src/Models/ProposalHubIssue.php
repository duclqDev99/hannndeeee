<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\InventoryDiscountPolicy\Models\InventoryDiscountPolicy;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalHubIssue extends BaseModel
{
    protected $table = 'hb_proposal_hub_issues';

    protected $fillable = [
        'general_order_code',
        'proposal_code',
        'warehouse_issue_id',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'is_warehouse',
        'quantity',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'reason_cancel',
        'proposal_issue_id',
        'status',
        'is_batch',
        'proposal_receipt_id',
        'policies_id',

    ];

    protected $casts = [
        'status' => ProposalIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function warehouseIssue()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_issue_id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposalHubIssueDetail()
    {
        return $this->hasMany(ProposalHubIssueDetail::class, 'proposal_id', 'id');
    }
    public function hubIssue()
    {
        if ($this->proposal_receipt_id) {
            return $this->belongsTo(HubIssue::class, 'id', 'proposal_id')->where('from_proposal_receipt', 1);
        }
        return $this->belongsTo(HubIssue::class, 'id', 'proposal_id')->where('from_proposal_receipt', 0);
    }
    public function policy()
    {
        return $this->belongsTo(InventoryDiscountPolicy::class, 'policies_id');
    }
}
