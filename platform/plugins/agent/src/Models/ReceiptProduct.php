<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ReceiptProduct extends BaseModel
{
    protected $table = 'agent_receipt_products';
    protected $fillable = [
        'general_order_code',
        'proposal_id',
        'warehouse_id',
        'warehouse_name',
        'warehouse_address',
        'isser_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'wh_departure_id',
        'wh_departure_name',
        'is_warehouse',
        'quantity',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
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
        return $this->hasMany(ReceiptProductDetail::class, 'receipt_id', 'id');
    }

    // public function proposal()
    // {
    //     return $this->hasOne(ProposalReceiptProducts::class, 'id', 'proposal_id');
    // }

    // public function actualReceipt(): HasOne
    // {
    //     return $this->hasOne(ActualReceipt::class, 'receipt_id', 'id');
    // }

    public function productBatch()
    {
        if($this->status == ApprovedStatusEnum::APPOROVED)
        {
            return $this->hasMany(AgentProductBatch::class, 'receipt_id', 'id');
        }
    }
}
