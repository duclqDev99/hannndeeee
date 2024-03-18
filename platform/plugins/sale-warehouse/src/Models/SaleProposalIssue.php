<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleProposalIssue extends BaseModel
{
    protected $table = 'sw_sale_proposal_issues';
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
        'status',
    ];
    protected $casts = [
        'status' => ProposalIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];


    public function warehouseIssue()
    {
        return $this->belongsTo(SaleWarehouseChild::class, 'warehouse_issue_id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposalHubIssueDetail()
    {
        return $this->hasMany(SaleProposalIssueDetail::class, 'proposal_id', 'id');
    }

    public function saleIssue()
    {
        return $this->belongsTo(SaleIssue::class, 'id', 'proposal_id');
    }

}
