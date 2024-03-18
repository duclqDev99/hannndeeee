<?php

namespace Botble\CustomerBookOrder\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\CustomerBookOrder\Enums\OrderStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class CustomerBookOrder extends BaseModel
{
    protected $table = 'hd_customer_book_order';

    protected $fillable = [
        'username',
        'email',
        'phone',
        'address',
        'type_order',
        'note',
        'quantity',
        'image',
        'expected_date',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'type_order' => OrderStatusEnum::class,
        'username' => SafeContent::class,
    ];
}