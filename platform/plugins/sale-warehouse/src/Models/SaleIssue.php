<?php

namespace Botble\SaleWarehouse\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleIssue extends BaseModel
{
    protected $table = 'sw_sale_issues';
    protected $fillable = [
        'warehouse_issue_id',
        'proposal_id',
        'warehouse_name',
        'warehouse_address',
        'general_order_code',
        'issuer_id',
        'invoice_issuer_name',
        'invoice_confirm_name',
        'warehouse_id',
        'warehouse_type',
        'issue_code',
        'quantity',
        'expected_date',
        'date_confirm',
        'title',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => ProductIssueStatusEnum::class,
        'name' => SafeContent::class,
    ];


    public function warehouseIssue()
    {
        return $this->belongsTo(SaleWarehouseChild::class, 'warehouse_issue_id');
    }

    public function warehouse()
    {
        return $this->morphTo('warehouse');
    }
    public function proposal()
    {
        return $this->hasOne(SaleProposalIssue::class, 'id', 'proposal_id');
    }
    function productIssueDetail()
    {
        return $this->hasMany(SaleIssueDetail::class, 'sale_isue_id', 'id');
    }
    // public function actualIssue()
    // {
    //     return $this->belongsTo(ActualIssue::class, 'id', 'hub_issue_id');
    // }
    public function actualQrCode()
    {
        return $this->hasMany(ActualIssueQrCode::class, 'issue_id', 'id');
    }
}
