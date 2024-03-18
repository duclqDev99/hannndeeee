<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Payment extends BaseModel
{
    protected $table = 'retail_payments';
   
    protected $fillable = [
        'amount',
        'status',
        'completed_at',
        'quotation_id',    
    ];

}
