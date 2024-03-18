<?php

namespace Botble\Sales\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderDetail extends BaseModel
{
    protected $table = 'hd_order_details';

    protected $fillable = [
        'id_order',
        'product_name',
        'product_size',
        'product_type',
        'quantity',
        'product_id',
        'type_model',
    ];

    protected $casts = [
        'product_name' => SafeContent::class,
        'product_size' => SafeContent::class,
        'product_type' => SafeContent::class,
        'type_model' => SafeContent::class,
    ];

    public $timestamps = false;

    public function product()
    {
        return $this->morphTo('product', 'type_model', 'product_id');
    }

}
