<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\OrderRetail\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderProduction extends BaseModel
{
    protected $table = 'retail_productions';

    protected $fillable = [
        'code',
        'order_code',
        'quotation_id',
        'status',
        'note',
        'created_by_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderProduction $production) {
            $production->code = static::generateUniqueCode($production);
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_code', 'code');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(OrderQuotation::class, 'quotation_id');
    }

    public static function get_order_code(int|string $productionId, OrderProduction $production): string
    {
        $prefixYCSX = substr($production->order_code, 1, 2);
        $prefix = 'ĐH' . $prefixYCSX;
        return '#' . $prefix . ((int)config('plugins.ecommerce.order.default_order_start_number') + $productionId);
    }

    public static function generateUniqueCode($production): string
    {
        $nextInsertId = BaseModel::determineIfUsingUuidsForId() ? static::query()->count() + 1 : static::query()->max(
            'id'
        ) + 1;

        do {
            $code = static::get_order_code($nextInsertId, $production);
            $nextInsertId++;
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

   
}
