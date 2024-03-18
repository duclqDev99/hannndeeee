<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleReceipt extends BaseModel
{
    protected $table = 'sw_sale_receipts';
  
    protected $fillable = [
        'warehouse_receipt_id',
        'hub_issue_id',
        'receipt_code',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'general_order_code',
        'quantity',
        'description',
        'expected_date',
        'date_confirm',
        'title',
        'status',
    ];

    protected $casts = [
        'status' => ApprovedStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function warehouseReceipt()
    {
        return $this->belongsTo(SaleWarehouseChild::class, 'warehouse_receipt_id');
    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }

    public function receiptDetail(): HasMany
    {
        return $this->hasMany(SaleReceiptDetail::class, 'sale_receipt_id', 'id');
    }
    public function hubIssue(){
        return $this->hasOne(HubIssue::class, 'id' , 'hub_issue_id');

    }
}
