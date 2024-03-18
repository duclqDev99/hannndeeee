<?php

namespace Botble\Showroom\Models;

use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomUser extends BaseModel
{
    protected $table = 'showroom_user';

    protected $fillable = [
        'user_id',
        'showroom_id',
    ];

}
