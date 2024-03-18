<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Warehouse\Enums\ProposalGoodIssueStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class MaterialOut extends BaseModel
{
    protected $table = 'wh_material_proposal_out';

    protected $fillable = [
        'warehouse_id',
        'warehouse_out_id',
        'warehouse_type',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'proposal_code',
        'document_number',
        'warehouse_name',
        'warehouse_address',
        'quantity',
        'total_amount',
        'tax_amount',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
        'general_order_code',
        'is_processing_house',
        'proposal_purchase_id',
        'issuer_id',
        'reason'
    ];

    protected $casts = [
        'name' => SafeContent::class,
        'status' => ProposalGoodIssueStatusEnum::class,
    ];

    public function proposalOutDetail(): HasMany
    {
        return $this->hasMany(MaterialOutDeatail::class, 'proposal_id', 'id');
    }

    public function warehouse_old(): BelongsTo
    {
        return $this->belongsTo(MaterialWarehouse::class,'warehouse_id','id');
    }

    public function warehouse()
    {
        return $this->morphTo('warehouse','warehouse_type','warehouse_out_id');
    }
    public function users()
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'user_request', 'id');
    }
    public function users_confirm()
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'user_confirm', 'id');
    }
    public function ware_house(): BelongsTo
    {
        return $this->belongsTo(MaterialOutConfirm::class, 'id', 'proposal_id')->withDefault();
    }
    public function proposalPurchase(): BelongsTo
    {
        return $this->belongsTo(MaterialProposalPurchase::class, 'id', 'proposal_out_id');

    }
    public function batchMaterials(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialBatch::class,
            MaterialOutDeatail::class,
            'proposal_id',
            'material_code',
            'id',
            'material_code'
        )->orderBy('created_at')->where('quantity','>','0');
    }
}
