<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentReceipt extends BaseModel
{
    protected $table = 'agent_receipts';
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
        'title' => SafeContent::class,
        'general_order_code' => SafeContent::class,
        'description' => SafeContent::class,
        'invoice_issuer_name' => SafeContent::class,
        'invoice_confirm_name' => SafeContent::class,
        'warehouse_name' => SafeContent::class,
        'warehouse_address' => SafeContent::class,
        'wh_departure_name' => SafeContent::class,
    ];

    public function receiptDetail(): HasMany
    {
        return $this->hasMany(AgentReceiptDetail::class, 'agent_receipt_id', 'id');
    }
    public function proposal()
    {
        if ($this->from_hub_warehouse == 0) {
            return $this->belongsTo(ProposalAgentReceipt::class, 'proposal_id');
        }
        return $this->belongsTo(ProposalHubIssue::class, 'proposal_id');
    }
    public function warehouseReceipt()
    {
        return $this->belongsTo(AgentWarehouse::class, 'warehouse_receipt_id', 'id');
    }
        public function warehouse()
        {
            return $this->morphTo('warehouse');
        }
    // public function proposal()
    // {
    //     return $this->hasOne(ProposalReceiptProducts::class, 'id', 'proposal_id');
    // }

    public function actualReceipt(): HasOne
    {
        return $this->hasOne(AgentActualReceipt::class, 'receipt_id', 'id');
    }

    public function productBatch()
    {
        if ($this->status == ApprovedStatusEnum::APPOROVED) {
            return $this->hasMany(AgentProductBatch::class, 'receipt_id', 'id');
        }
    }
}
