<?php

namespace Botble\HubWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class HubIssue extends BaseModel
{
    protected $table = 'hb_hub_issues';

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
        'from_proposal_receipt',
        'title',
        'description',
        'expected_date',
        'status',
        'reason_cancel',
        'is_batch',
        'issue_code',
        'date_confirm'
    ];

    protected $casts = [
        'status' => ProductIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];
    public function warehouseIssue()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_issue_id');
    }

    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposal()
    {
        return $this->hasOne(ProposalHubIssue::class, 'id', 'proposal_id');
    }
    function productIssueDetail()
    {
        return $this->hasMany(HubIssueDetail::class, 'hub_issue_id', 'id');
    }
    public function actualIssue()
    {
        return $this->belongsTo(ActualIssue::class, 'id', 'hub_issue_id');
    }
    public function actualQrCode()
    {
        return $this->hasMany(ActualIssueQrCode::class, 'issue_id', 'id');
    }
    public function quantity(): Attribute
    {
        return Attribute::get(function () {
            return $this->productIssueDetail()->sum('quantity');
        });
    }
}
