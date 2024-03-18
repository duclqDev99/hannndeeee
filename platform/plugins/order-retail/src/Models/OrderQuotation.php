<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\OrderRetail\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderQuotation extends BaseModel
{
    protected $table = 'retail_quotations';
  
    protected $fillable = [
        'title',
        'amount',
        'start_date',
        'due_date',
        'shipping_amount',
        'note',
        'order_code',
        'status',     
        'created_by_id'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_code', 'code');
    }
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'quotation_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'quotation_id');
    } 

    public function getIsEffectiveAttribute()
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($this->start_date);
        return $now->gte($startDate);
    }

    public function getIsNotDueAttribute()
    {
        $now = Carbon::now();
        $dueDate = Carbon::parse($this->due_date);
        return $now->lt($dueDate);
    }}
