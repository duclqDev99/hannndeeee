<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Warehouse\Enums\PurchaseOrderStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ReceiptPurchaseGoods extends BaseModel
{
    protected $table = 'wh_receipt_goods';

    protected $fillable = [
        'warehouse_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'general_order_code',
        'proposal_id',
        'document_number',
        'warehouse_name',
        'warehouse_address',
        'quantity',
        'total_amount',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatusEnum::class,
        'invoice_issuer_name' => SafeContent::class,
        'invoice_confirm_name' => SafeContent::class,
        'document_number' => SafeContent::class,
        'warehouse_name' => SafeContent::class,
        'warehouse_address' => SafeContent::class,
        'general_order_code' => SafeContent::class,
        'code' => SafeContent::class,
        'title' => SafeContent::class,
        'description' => SafeContent::class,
    ];


    public function receiptDetail(): HasMany
    {
        return $this->hasMany(ReceiptPurchaseGoodsDetail::class, 'receipt_id', 'id');
    }

    public function proposal()
    {
        return $this->belongsTo(ProposalPurchaseGoods::class, 'proposal_id', 'id');
    }
}
