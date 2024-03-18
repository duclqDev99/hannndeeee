<?php

namespace Botble\Agent\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class AgentUser extends BaseModel
{
    protected $table = 'agent_user';

    protected $fillable = [
        'user_id',
        'agent_id',
    ];

    public function agent(){
        return $this->belongsTo(Agent::class,'agent_id');
    }

}
