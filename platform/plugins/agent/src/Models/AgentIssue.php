<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentIssue extends BaseModel
{
    protected $table = 'agent_issues';

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
    ];
    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function warehouseIssue()
    {
        return $this->belongsTo(AgentWarehouse::class, 'warehouse_issue_id', 'id');
    }
    public function productIssueDetail()
    {
        return $this->hasMany(AgentIssueDetail::class, 'agent_issue_id');
    }
    public function proposal(){
        return $this->belongsTo(AngentProposalIssue::class,'proposal_id','id');
    }
    public function quantity(): Attribute
    {
        return Attribute::get(function () {
            return $this->productIssueDetail()->sum('quantity');
        });
    }
}
