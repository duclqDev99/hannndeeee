<?php

namespace Botble\WarehouseFinishedProducts\Models;


use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualIssue extends BaseModel
{
    protected $table = 'wfp_actual_issue';

    protected $fillable = [
        'product_issue_id',
        'general_order_code',
        'warehouse_issue_id',
        'warehouse_name',
        'warehouse_address',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'is_warehouse',
        'reason',
        'status',
        'image',
    ];


    public function autualDetail()
    {
        return $this->hasMany(ActualIssueDetail::class, 'actual_id', 'id');
    }
    public function issue()
    {
        return $this->hasOne(ProductIssue::class, 'id', 'product_issue_id');

    }

    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->issue()->first()->title;
        });
    }
}
