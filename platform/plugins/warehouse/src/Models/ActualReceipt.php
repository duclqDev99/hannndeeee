<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceipt extends BaseModel
{
    protected $table = 'wh_actual_receipt';

    protected $fillable = [
        'receipt_id',
        'general_order_code',
        'warehouse_id',
        'warehouse_name',
        'warehouse_address',
        'invoice_confirm_name',
        'quantity',
        'status',
    ];

    protected $casts = [
        'status' => MaterialProposalStatusEnum::class,
        'invoice_confirm_name' => SafeContent::class,
        'warehouse_name' => SafeContent::class,
        'warehouse_address' => SafeContent::class,
        'general_order_code' => SafeContent::class,
    ];

    public function autualDetail()
    {
        return $this->hasMany(ActualReceiptDetail::class, 'actual_id', 'id');
    }

    // public function getActualMaterialById($material_id)
    // {
    //     $materialDetail = $this->hasMany(ActualReceiptDetail::class, 'actual_id', 'id');

    //     return $materialDetail->where(['material_id' => $material_id])->first();
    // }
}
