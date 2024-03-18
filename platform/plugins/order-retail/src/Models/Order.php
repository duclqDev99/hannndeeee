<?php

namespace Botble\OrderRetail\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Department\Models\OrderDepartment;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\OrderAnalysis\Models\OrderAttach;
use Botble\OrderRetail\Enums\OrderType;
use Botble\OrderRetail\Enums\OrderTypeEnum;
use Botble\OrderStepSetting\Models\Action;
use Botble\OrderStepSetting\Models\Step;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Enums\TypeOrderEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Order extends BaseModel
{
    protected $table = 'retail_orders';

    protected $fillable = [
        'code',
        'order_type',
        'customer_name',
        'customer_phone',
        'expected_date',
        'status',
        'total_qty',
        'amount',
        'tax_amount',
        'note',
        'coupon_code',
        'discount_amount',
        'sub_total',
        'discount_description',
        'payment_id',
        'user_id',
        'order_parent_id',
        'created_by_id'
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'code' => SafeContent::class,
        'description' => SafeContent::class,
        'order_type' => OrderTypeEnum::class,
    ];

    protected static function booted(): void
    {
        self::deleted(function (Order $order) {
            $order->steps()->each(fn (Step $step) => $step->delete());
            $order->products()->each(fn (OrderProduct $product) => $product->delete());
            $order->quotation()->delete();
            $order->production()->delete();
        });

        static::creating(function (Order $order) {
            $order->code = static::generateUniqueCode($order);
        });
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class, 'order_id');
    }

    public function currStep()
    {
        return $this->hasOne(Step::class, 'order_id')
            ->where('is_ready', true)
            ->latest('index');
    }

    public function lastAction(): HasOneThrough
    {
        return $this->hasOneThrough(
            Action::class,
            Step::class,
            'order_id',
            'step_id',
            'id',
            'id'
        )->latest('handled_at');
    }

    public function customer(): HasOne{
        return $this->hasOne(Customer::class, 'order_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

 
    public function quotation(): HasOne
    {
        return $this->hasOne(OrderQuotation::class, 'order_code' , 'code');
    }

    public function production(): HasOne
    {
        return $this->hasOne(OrderProduction::class, 'order_code', 'code');
    }

    // public function order_detail(): HasMany
    // {
    //     return $this->hasMany(OrderDetail::class, 'id_order', 'id');
    // }

    // public function customer(): BelongsTo
    // {
    //     return $this->belongsTo(Customer::class, 'id_user', 'id');
    // }

    public function isInvoiceAvailable(): bool
    {
        return $this->invoice()->exists() && (!EcommerceHelper::disableOrderInvoiceUntilOrderConfirmed());
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'reference_id')->withDefault();
    }

    public function orderLink()
    {
        if ($this->type_order != TypeOrderEnum::SAMPLE) {
            return $this->hasOne(Order::class, 'id', 'link_order_id');
        }
        return $this;
    }

    public function attachs(string $model = null)
    {
        if (!empty($model)) {
            return $this->hasMany(OrderAttach::class, 'order_id', 'id')->where('attach_type', $model)->get();
        }
        return $this->hasMany(OrderAttach::class, 'order_id', 'id');
    }

    public function attachByType(string $typeName)
    {
        return $this->hasMany(OrderAttach::class, 'order_id', 'id')->where('attach_type', $typeName);
    }

    // lấy lịch Sử của đơn
    // public function histories()
    // {
    //     return $this->hasMany(OrderHistory::class, 'order_id', 'id');
    // }

 

    public static function get_order_code(int|string $orderId, Order $order): string
    {
        $prefix = '';
        switch ($order->order_type->getValue()) {
            case 'sale_promotion';
                $prefix = 'SP';
                break;
            case 'sale_club';
                $prefix = 'SC';
                break;
            case 'sale_transfer';
                $prefix = 'ST';
                break;
            case 'sale_fashion';
                $prefix = 'SF';
                break;
        }
        return '#' . $prefix . ((int)config('plugins.ecommerce.order.default_order_start_number') + $orderId);
    }

    public static function generateUniqueCode($order): string
    {
        $nextInsertId = BaseModel::determineIfUsingUuidsForId() ? static::query()->count() + 1 : static::query()->max(
            'id'
        ) + 1;

        do {
            $code = static::get_order_code($nextInsertId, $order);
            $nextInsertId++;
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }
}
