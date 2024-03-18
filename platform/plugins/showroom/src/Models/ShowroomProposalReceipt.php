<?php

namespace Botble\Showroom\Models;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomProposalReceipt extends BaseModel
{
    protected $table = 'showroom_proposal_receipts';

    protected $fillable = [
        'id',
        'warehouse_receipt_id',
        'proposal_code',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'general_order_code',
        'quantity',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
        'warehouse_id',
        'warehouse_type',
        'reason_cancel',
        'expected_date_submit'
    ];

    protected $casts = [
        'status' => ProposalAgentEnum::class,
    ];
    public function proposalReceiptDetail()
    {
        return $this->hasMany(ProposalShowroomReceiptDetail::class, 'proposal_id');
    }
    public function warehouseReceipt()
    {
        return $this->hasOne(ShowroomWarehouse::class, 'id', 'warehouse_receipt_id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposalIssue()
    {
        return $this->belongsTo(ProposalHubIssue::class, 'id', 'proposal_receipt_id')->where(function ($query) {
            $query->where('warehouse_type', ShowroomWarehouse::class);
        });
    }

}
