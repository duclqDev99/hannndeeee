<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class HubReceipt extends BaseModel
{
    protected $table = 'hb_hub_receipt';
    protected $fillable = [
        'warehouse_receipt_id',
        'proposal_id',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'general_order_code',
        'quantity',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
        'receipt_code',
        'reason',
        'is_batch',
        'issue_id'

    ];

    protected $casts = [
        'status' => ApprovedStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function warehouseReceipt()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_receipt_id');
    }
    public function receiptDetail(){
        return $this->hasMany(HubReceiptDetail::class, 'hub_receipt_id', 'id');

    }
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposal()
    {
        if($this->warehouse_type == WarehouseFinishedProducts::class)
        {
            return $this->hasOne(ProposalProductIssue::class, 'id', 'proposal_id');
        }
        return $this->hasOne(ProposalHubReceipt::class, 'id', 'proposal_id');
    }

    public function issue(){
        return $this->hasOne(ProductIssue::class, 'id', 'issue_id');
    }

    public function productBatch()
    {
        return $this->hasMany(ProductBatch::class, 'receipt_id', 'id');
    }

    public function batch(){
        return $this->hasOne(ProductBatch::class, 'id', 'batch_id');
    }

    public function actualReceipt()
    {
        return $this->hasOne(ActualReceipt::class, 'receipt_id', 'id');
    }
    public function qrCode()
    {
        return $this->hasMany(ActualReceiptQrcode::class, 'receipt_id', 'id');
    }
}
