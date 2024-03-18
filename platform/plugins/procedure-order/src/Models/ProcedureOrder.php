<?php

namespace Botble\ProcedureOrder\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Sales\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProcedureOrder extends BaseModel
{
    use SoftDeletes;
    protected $table = 'procedure_orders';

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'department_code',
        'department_joins',
        'next_step',
        'main_thread_status',
        'cycle_point',
        'location',
        'group_code',
    ];

    protected $casts = [
        'name' => SafeContent::class,
        'code' => SafeContent::class,
        'department_code' => SafeContent::class,
        'main_thread_status' => SafeContent::class,
        'next_step' => 'json',
        'location' => 'json',
    ];
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProcedureOrder::class,'parent_id', 'id');
    }

    public function orders(): BelongsToMany
    {
        return $this
            ->belongsToMany(Order::class, 'hd_order_reference_procedure', 'procedure_code', 'order_id', 'code', 'id');
    }
}
