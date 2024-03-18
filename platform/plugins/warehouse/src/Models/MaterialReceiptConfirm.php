<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Warehouse\Enums\MaterialReceiptStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialReceiptConfirm extends BaseModel
{
    protected $table = 'wh_material_receipt_confirm';

    protected $fillable = [
        'general_order_code',
        'warehouse_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'proposal_id',
        'document_number',
        'warehouse_name',
        'warehouse_address',
        'wh_departure_id',
        'wh_departure_name',
        'is_from_supplier',
        'quantity',
        'total_amount',
        'tax_amount',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'is_purchase_goods',
        'status',
    ];

    protected $casts = [
        'status' => MaterialReceiptStatusEnum::class,
        'invoice_issuer_name' => SafeContent::class,
        'invoice_confirm_name' => SafeContent::class,
        'wh_departure_name' => SafeContent::class,
        'document_number' => SafeContent::class,
        'warehouse_name' => SafeContent::class,
        'warehouse_address' => SafeContent::class,
        'proposal_code' => SafeContent::class,
        'title' => SafeContent::class,
        'description' => SafeContent::class,
    ];

    public function receiptDetail(): HasMany
    {
        return $this->hasMany(MaterialReceiptConfirmDetail::class, 'receipt_id', 'id');
    }

    public function actualReceipt(): HasOne
    {
        return $this->hasOne(ActualReceipt::class, 'receipt_id', 'id');
    }

    public function proposal()
    {
        if($this->is_purchase_goods === 1){
            return $this->belongsTo(ReceiptPurchaseGoods::class, 'proposal_id', 'id');
        }
        return $this->belongsTo(MaterialProposalPurchase::class, 'proposal_id', 'id');
    }
}
