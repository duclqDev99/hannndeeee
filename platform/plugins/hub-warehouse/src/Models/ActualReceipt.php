<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceipt extends BaseModel
{
    protected $table = 'hb_actual_receipts';
    protected $fillable = [
        'general_order_code',
        'receipt_id',
        'warehouse_receipt_id',
        'warehouse_name',
        'warehouse_address',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'is_warehouse',
        'quantity',
        'status',
        'image'
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
    public function batch(){
        return $this->hasMany(HubActualReceiptBatch::class, 'actual_id');
    }
    public function receipt(){
        return $this->hasOne(HubReceipt::class, 'id', 'receipt_id');
    }
    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->receipt()->first()->title;
        });
    }
}
