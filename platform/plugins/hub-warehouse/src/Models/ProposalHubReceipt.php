<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalHubReceipt extends BaseModel
{
    protected $table = 'hb_proposal_hub_recepits';

    protected $fillable = [
        'general_order_code',
        'proposal_code',
        'warehouse_receipt_id',
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
        'is_batch'
    ];

    protected $casts = [
        'status' => ProposalReceiptProductEnum::class,
        'name' => SafeContent::class,
    ];
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }

    public function warehouseReceipt()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_receipt_id');
    }
    public function proposalReceiptDetail()
    {
        return $this->hasMany(ProposalHubReceiptDetail::class, 'proposal_id', 'id');
    }

}
