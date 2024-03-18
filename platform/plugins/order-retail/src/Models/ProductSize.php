<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProductSize extends BaseModel
{
    protected $table = 'retail_product_sizes';
 
    protected $fillable = [
        'value',
        'quantity',
        'retail_product_id'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class, 'retail_product_id');
    }

  
}
