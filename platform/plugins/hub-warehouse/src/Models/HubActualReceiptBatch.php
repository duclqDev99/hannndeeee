<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class HubActualReceiptBatch extends BaseModel
{
    protected $table = 'hb_actual_receipt_batch';
    protected $fillable = [
        'actual_id',
        'batch_id',
        'quantity',
        'start_qty',
    ];

    public function productInBatch()
    {
        return $this->hasMany(ActualReceiptQrcode::class, 'batch_id', 'batch_id');
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function actual()
    {
        return $this->hasOne(ActualReceipt::class, 'actual_id');
    }
}
