<?php

namespace Botble\Sales\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Sales\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderHistory extends BaseModel
{
    protected $table = 'hd_order_history';

    protected $fillable = [
        'order_id',
        'procedure_code_previous',
        'procedure_name_previous',
        'procedure_code_current',
        'procedure_name_current',
        'created_by',
        'created_by_name',
        'status',
        'description',
    ];

    protected $casts = [
        'procedure_code_previous' => SafeContent::class,
        'procedure_name_previous' => SafeContent::class,
        'procedure_code_current' => SafeContent::class,
        'procedure_name_current' => SafeContent::class,
        'created_by_name' => SafeContent::class,
    ];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'order_id', 'id')->withDefault();
    }
}
