<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Enums\PurchaseOrderStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProposalPurchaseGoods extends BaseModel
{
    protected $table = 'wh_purchase_goods';

    protected $fillable = [
        'warehouse_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'general_order_code',
        'code',
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
        'reasoon_cancel',
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
        'reasoon_cancel' => SafeContent::class,
    ];


    public function proposalDetail(): HasMany
    {
        return $this->hasMany(ProposalPurchaseGoodsDetail::class, 'proposal_id', 'id');
    }
}
