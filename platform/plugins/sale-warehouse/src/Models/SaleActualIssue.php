<?php

namespace Botble\SaleWarehouse\Models;


use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class SaleActualIssue extends BaseModel
{
    protected $table = 'sw_actual_issue';
    protected $fillable = [
        'sale_issue_id',
        'image',
        'reason'
    ];


    public function autualDetail()
    {
        return $this->hasMany(SaleActualIssueDetail::class, 'actual_id', 'id');
    }
    public function issue(){
        return $this->hasOne(SaleIssue::class, 'id', 'sale_issue_id');
    }
    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->issue()->first()->title;
        });
    }
}
