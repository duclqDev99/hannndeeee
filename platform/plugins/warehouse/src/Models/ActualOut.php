<?php

namespace Botble\Warehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualOut extends BaseModel
{
    protected $table = 'wh_actual_outs';

    protected $fillable = [
        'confirm_out_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'quantity',
        'total_amount',
        'warehouse_id',
        'warehouse_name',
        'date_confirm',
        'status',
        'general_order_code',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function receiptDetail(): HasMany
    {
        return $this->hasMany(ActualOutDetail::class, 'actual_out_id', 'id')->with('detailBatchMaterial');
    }



}
