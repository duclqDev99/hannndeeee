<?php

namespace Botble\Agent\Models;


use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentActualIssue extends BaseModel
{
    protected $table = 'agent_actual_issues';
    protected $fillable = [
        'anget_issue_id',
        'image',
    ];


    public function autualDetail()
    {
        return $this->hasMany(AgentActualIssueDetail::class, 'actual_id', 'id');
    }
    public function issue()
    {
        return $this->hasOne(AgentIssue::class, 'id', 'anget_issue_id');

    }

    public function title(): Attribute{
        return Attribute::get(function(){
            return $this->issue()->first()->title;
        });
    }
}
