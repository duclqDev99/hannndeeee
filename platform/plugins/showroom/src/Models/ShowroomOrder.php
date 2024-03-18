<?php

namespace Botble\Showroom\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomOrder extends BaseModel
{
    protected $table = 'showroom_orders';

    protected $fillable = [
        'order_id',
        'where_type',
        'where_id',
        'list_id_product_qrcode',
        'list_id_product_qrcode_sale',
    ];

    protected $appends = ['status'];

    protected $casts = [
        'list_id_product_qrcode' => 'json',
        'list_id_product_qrcode_sale' => 'json',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function where()
    {
        return $this->morphTo();
    }
    
    public function location()
    {
        return $this->morphTo('location', 'where_type', 'where_id');
    }

    public function getStatusAttribute()
    {
        $order = $this->order()->with('payment')->first();
        if ($order && $order->status->getValue() === OrderStatusEnum::COMPLETED && $order->payment && $order->payment->status->getValue() === PaymentStatusEnum::COMPLETED) {
            return 'completed';
        }

        return 'pending';
    }
}
