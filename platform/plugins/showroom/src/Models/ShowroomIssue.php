<?php

namespace Botble\Showroom\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomIssue extends BaseModel
{
    protected $table = 'showroom_issues';

    protected $fillable = [
        'warehouse_issue_id',
        'proposal_id',
        'warehouse_name',
        'warehouse_address',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'general_order_code',
        'title',
        'description',
        'expected_date',
        'date_confirm',
        'status',
        'reason',
        'issue_code',
    ];

    protected $casts = [
        'status' => ProductIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function warehouseIssue()
    {
        return $this->belongsTo(ShowroomWarehouse::class, 'warehouse_issue_id', 'id');
    }
    public function productIssueDetail()
    {
        return $this->hasMany(ShowroomIssueDetail::class, 'showroom_issue_id');
    }
    public function proposal()
    {
        return $this->belongsTo(ShowroomProposalIssue::class, 'proposal_id', 'id');
    }
    public function actualReceipt()
    {
        return $this->hasOne(ShowroomActualIssue::class, 'showroom_issue_id');
    }
    public function quantity(): Attribute
    {
        return Attribute::get(function () {
            return $this->productIssueDetail()->sum('quantity');
        });
    }
}
