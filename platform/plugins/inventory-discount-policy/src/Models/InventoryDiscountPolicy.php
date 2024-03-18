<?php

namespace Botble\InventoryDiscountPolicy\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class InventoryDiscountPolicy extends BaseModel
{
    protected $table = 'plc_inventory_discount_policies';
    protected $fillable = [
        'name',
        'status',
        'start_date',
        'end_date',
        'type_warehouse',
        'type_date_active',
        'time_active',
        'type_time',
        'quantity',
        'quantity_done',
        'type_option',
        'discount_on',
        'value',
        'status',
        'code',
        'target',
        'product_category_id',
        'document',
        'product',
        'image',
        'customer_class_type',
        'apply_for'
    ];

    protected $casts = [
        'status' => HubStatusEnum::class,
        'name' => SafeContent::class,
    ];
}
