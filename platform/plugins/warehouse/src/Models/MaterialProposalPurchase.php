<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialProposalPurchase extends BaseModel
{
    protected $table = 'wh_material_proposal_purchase';

    protected $fillable = [
        'general_order_code',
        'warehouse_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'proposal_code',
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
        'status',
        'reasoon_cancel',
        'proposal_out_id',
    ];

    protected $casts = [
        'status' => MaterialProposalStatusEnum::class,
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
    public function proposalDetail(): HasMany
    {
        return $this->hasMany(MaterialProposalPurchaseDetail::class, 'proposal_id', 'id');
    }

    public function ware_house(): BelongsTo
    {
        return $this->belongsTo(MaterialReceiptConfirm::class, 'id', 'proposal_id')->withDefault();
    }

    public function proposalOut(): HasOne
    {
        return $this->hasOne(MaterialOut::class, 'proposal_purchase_id', 'id');
    }
}
