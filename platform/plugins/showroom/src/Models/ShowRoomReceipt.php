<?php

namespace Botble\Showroom\Models;

use Botble\Base\Models\BaseModel;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowRoomReceipt extends BaseModel
{
    protected $table = 'showroom_receipts';
    protected $fillable = [
        'warehouse_receipt_id',
        'proposal_id',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'general_order_code',
        'quantity',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'reason_cancel',
        'receipt_code',
        'status',
        'from_hub_warehouse'
    ];

    protected $casts = [
        'status' => ApprovedStatusEnum::class,

    ];

    public function receiptDetail(): HasMany
    {
        return $this->hasMany(ShowRoomReceiptDetail::class, 'showroom_receipt_id', 'id');
    }

    public function warehouseReceipt()
    {
        return $this->belongsTo(ShowroomWarehouse::class, 'warehouse_receipt_id', 'id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposal()
    {
        if ($this->from_hub_warehouse == 0) {
            return $this->belongsTo(ShowroomProposalReceipt::class, 'proposal_id');
        }
        return $this->belongsTo(ProposalHubIssue::class, 'proposal_id');
    }
    // public function proposal()
    // {
    //     return $this->hasOne(ProposalReceiptProducts::class, 'id', 'proposal_id');
    // }

    public function actualReceipt(): HasOne
    {
        return $this->hasOne(ShowroomActualReceipt::class, 'receipt_id', 'id');
    }

    public function hubIssue()
    {
        if ($this->from_hub_warehouse == 0) {
            return $this->belongsTo(HubIssue::class, 'proposal_id', 'proposal_id')->where([
                'from_proposal_receipt' => 1,
                'warehouse_type' => ShowroomWarehouse::class
            ]);
        }
        return $this->belongsTo(HubIssue::class, 'proposal_id', 'proposal_id')->where([
            'from_proposal_receipt' => 0,
            'warehouse_type' => ShowroomWarehouse::class
        ]);
    }

    // public function productBatch()
    // {
    //     if ($this->status == ApprovedStatusEnum::APPOROVED) {
    //         return $this->hasMany(AgentProductBatch::class, 'receipt_id', 'id');
    //     }
    // }
}
