<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomProposalIssue extends BaseModel
{
    protected $table = 'showroom_proposal_issues';

    protected $fillable = [
        'warehouse_issue_id',
        'warehouse_name',
        'warehouse_address',
        'proposal_code',
        'general_order_code',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'quantity',
        'title',
        'expected_date',
        'date_confirm',
        'is_batch',
        'reason_cancel',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => ProposalIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];
    public function proposalAgentIssueDetail()
    {
        return $this->hasMany(ShowroomProposalIssueDetail::class, 'proposal_id', 'id');
    }
    public function warehouseIssue()
    {
        return $this->belongsTo(ShowroomWarehouse::class, 'warehouse_issue_id', 'id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
}
