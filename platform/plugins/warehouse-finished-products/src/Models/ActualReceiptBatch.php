<?php

namespace Botble\WarehouseFinishedProducts\Models;

use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualReceiptBatch extends BaseModel
{
    protected $table = 'wfp_actual_receipt_batch';
    protected $fillable = [
        'actual_id',
        'batch_id',
        'quantity',
        'start_qty',
    ];
    
    public function batch()
    {
        return $this->hasOne(ProductBatch::class, 'id','batch_id');
    }


    public function actual()
    {
        return $this->hasOne(ActualReceipt::class, 'actual_id');
    }
}
