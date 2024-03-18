<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Forms\Fields\ProposalProductField;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceipt extends BaseModel
{
    protected $table = 'wfp_actual_receipt';
    protected $fillable = [
        'general_order_code',
        'receipt_id',
        'warehouse_id',
        'warehouse_name',
        'warehouse_address',
        'invoice_confirm_name',
        'wh_departure_id',
        'wh_departure_name',
        'is_warehouse',
        'quantity',
        'status',
        'image',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'general_order_code' => SafeContent::class,
        'invoice_confirm_name' => SafeContent::class,
        'warehouse_name' => SafeContent::class,
        'warehouse_address' => SafeContent::class,
        'wh_departure_name' => SafeContent::class,
    ];

    public function actualDetail()
    {
        return $this->hasMany(ActualReceiptDetail::class, 'actual_id');
    }

    public function receipt()
    {
        return $this->hasOne(ReceiptProduct::class, 'id', 'receipt_id');
    }

    public function batchs()
    {
        return $this->hasMany(ActualReceiptBatch::class, 'actual_id');
    }

    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->receipt()->first()->title;
        });
    }
}
