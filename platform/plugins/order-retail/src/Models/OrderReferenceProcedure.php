<?php

namespace Botble\Sales\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Botble\Department\Models\OrderDepartment;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Enums\TypeOrderEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class OrderReferenceProcedure extends BaseModel
{
    protected $table = 'hd_order_reference_procedure';

    protected $fillable = [
        'order_id',
        'procedure_code',
    ];

    protected $casts = [
        'procedure_code' => SafeContent::class,
    ];

    public $timestamps = false;

    
}
