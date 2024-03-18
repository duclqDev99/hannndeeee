<?php

namespace Botble\Showroom\Models;

use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomCustomer extends BaseModel
{
    protected $table = 'showroom_customers';

    protected $fillable = [
        'customer_id',
        'where_type',
        'where_id',
    ];
}
