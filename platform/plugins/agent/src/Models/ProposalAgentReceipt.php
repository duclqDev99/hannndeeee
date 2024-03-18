<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalAgentReceipt extends BaseModel
{
    protected $table = 'agent_proposal_receipts';

    protected $fillable = [
        'id',
        'warehouse_receipt_id',
        'proposal_code',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'warehouse_type',
        'warehouse_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'general_order_code',
        'quantity',
        'title',
        'description',
        'expected_date',
        'expected_date_submit',
        'date_confirm',
        'status',
        'reason_cancel'
    ];

    protected $casts = [
        'status' => ProposalAgentEnum::class,
    ];

    public function warehouseReceipt()
    {
        return $this->belongsTo(AgentWarehouse::class, 'warehouse_receipt_id');
    }
    public function proposalReceiptDetail()
    {
        return $this->hasMany(ProposalAgentReceiptDetail::class, 'proposal_id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposalIssue()
    {
        return $this->belongsTo(ProposalHubIssue::class, 'id','proposal_receipt_id')->where(function ($query) {
            $query->where('warehouse_type', AgentWarehouse::class);
        });
    }
}
