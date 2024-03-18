<?php

namespace Botble\Showroom\Models;

use App\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Order;
use Botble\HubWarehouse\Models\HubWarehouse;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ShowroomPickAddress extends BaseModel
{
    protected $table = 'showroom_pick_addresses';
    
    protected $fillable = [
        'service_type',
        'pick_name',
        'pick_email',
        'pick_address_id',
        'province_id',
        'district_id',
        'ward_id',
        'showroom_code',
    ];
}
