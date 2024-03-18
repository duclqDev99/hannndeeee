<?php

namespace Botble\Payment\Models;

use Botble\ACL\Models\User;
use Botble\Base\Facades\Html;
use Botble\Base\Models\BaseModel;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Showroom\Models\Showroom;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends BaseModel
{
    protected $table = 'payments';

    protected $fillable = [
        'amount',
        'currency',
        'user_id',
        'charge_id',
        'payment_channel',
        'description',
        'status',
        'order_id',
        'payment_type',
        'customer_id',
        'customer_type',
        'refunded_amount',
        'refund_note',
        'content_banking',
        'is_refunded_point',
        'refunded_point_amount',
        'images',
    ];

    protected $casts = [
        'payment_channel' => PaymentMethodEnum::class,
        'status' => PaymentStatusEnum::class,
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function customer(): MorphTo
    {
        return $this->morphTo()->withDefault();
    }

    public function showroomOrder(){
        return $this->belongsTo(Showroom::class)->withDefault();
    }

    public function getDescription(): string
    {
        $time = Html::tag('span', $this->created_at->diffForHumans(), ['class' => 'small italic']);

        return __('You have created a payment #:charge_id via :channel :time : :amount', [
            'charge_id' => $this->charge_id,
            'channel' => $this->payment_channel->label(),
            'time' => $time,
            'amount' => number_format($this->amount, 2) . $this->currency,
        ]);
    }
    protected function images(): Attribute
    {
        return Attribute::make(
            get: function (string|null $value): array {
                try {
                    if ($value === '[null]') {
                        return [];
                    }

                    $images = json_decode((string)$value, true);

                    if (is_array($images)) {
                        $images = array_filter($images);
                    }

                    return $images ?: [];
                } catch (Exception) {
                    return [];
                }
            }
        );
    }
}
