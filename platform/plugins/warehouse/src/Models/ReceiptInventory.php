<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ReceiptInventory extends BaseModel
{
    protected $table = 'inventory_receipt';

    protected $fillable = [
        'user_id',
        'user_confirm_id',
        'proposal_code',
        'inventory_id',
        'supplier_id',
        'amount',
        'description',
        'material_id',
        'quantity',
        'price_import',
        'expected_date',
        'date_confirm',
        'status',
        'date_improve',
    ];

    protected $casts = [
        'status' => SafeContent::class,
        'proposal_code' => SafeContent::class,
        'description' => SafeContent::class,
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Receipt::class,'proposal_code', 'proposal_code');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(MaterialWarehouse::class,'inventory_id', 'id')->withDefault();
    }

    public function materials(): BelongsTo
    {
        return $this->belongsTo(Material::class,'material_id', 'id');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'user_id', 'id');
    }

    public function userConfirm(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'user_confirm_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id')->withDefault();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'reference_id')->withDefault();
    }
}
