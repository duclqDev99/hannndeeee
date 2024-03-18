<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalReceiptProducts extends BaseModel
{
    protected $table = 'wfp_proposal_receipt_products';
    protected $fillable = [
        'general_order_code',
        'proposal_code',
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
        'reasoon_cancel',
        'proposal_issue_id',
        'status',
    ];

    protected $casts = [
        'status' => ProposalReceiptProductEnum::class,
        'title' => SafeContent::class,
        'general_order_code' => SafeContent::class,
        'description' => SafeContent::class,
        'reasoon_cancel' => SafeContent::class,
        'invoice_issuer_name' => SafeContent::class,
        'invoice_confirm_name' => SafeContent::class,
        'warehouse_name' => SafeContent::class,
        'warehouse_address' => SafeContent::class,
        'wh_departure_name' => SafeContent::class,
    ];

    public function proposalDetail(): HasMany
    {
        return $this->hasMany(ProposalReceiptProductDetails::class, 'proposal_id', 'id');
    }
    public function warehouseQR(){
        return $this->belongsTo(WarehouseFinishedProducts::class,'warehouse_id');
    }

}
