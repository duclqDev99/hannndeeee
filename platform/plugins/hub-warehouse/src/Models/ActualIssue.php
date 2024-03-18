<?php

namespace Botble\HubWarehouse\Models;


use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ActualIssue extends BaseModel
{
    protected $table = 'hb_actual_issue';
    protected $fillable = [
        'hub_issue_id',
        'image',
        'reason'
    ];


    public function autualDetail()
    {
        return $this->hasMany(ActualIssueDetail::class, 'actual_id', 'id');
    }
    public function issue(){
        return $this->hasOne(HubIssue::class, 'id', 'hub_issue_id');
    }
    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->issue()->first()->title;
        });
    }
}
