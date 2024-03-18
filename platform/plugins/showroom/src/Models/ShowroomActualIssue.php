<?php

namespace Botble\Showroom\Models;


use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomActualIssue extends BaseModel
{
    protected $table = 'showroom_actual_issues';
    protected $fillable = [
        'showroom_issue_id',
        'image',
    ];


    public function autualDetail()
    {
        return $this->hasMany(ShowroomActualIssueDetail::class, 'actual_id', 'id');
    }
    public function issue(){
        return $this->hasOne(ShowroomIssue::class, 'id', 'showroom_issue_id');
    }
    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->issue()->first()->title;
        });
    }
}
