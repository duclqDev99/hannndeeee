<?php

namespace Botble\Agent\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentActualReceipt extends BaseModel
{
    protected $table = 'agent_actual_receipt';
    protected $fillable = [
        'receipt_id',
        'image',

    ];

    public function actualDetail()
    {
        return $this->hasMany(AgentActualReceiptDetail::class, 'actual_id', 'id');
    }

    public function receipt()
    {
        return $this->belongsTo(AgentReceipt::class, 'receipt_id', 'id');
    }
    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->issue()->first()->title;
        });
    }
}
