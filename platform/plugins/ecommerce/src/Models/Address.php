<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Traits\LocationTrait;
use Botble\Ecommerce\Traits\LocationViettelTrait;
use Botble\Location\Models\District;
use Botble\Location\Models\Province;
use Botble\Location\Models\Ward;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends BaseModel
{
    use LocationTrait;
    use LocationViettelTrait;

    protected $table = 'ec_customer_addresses';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'country',
        'state',
        'city',
        'address',
        'zip_code',
        'customer_id',
        'is_default',
        'province_id',
        'district_id',
        'ward_id',
        'showroom_id'
    ];
    
}
