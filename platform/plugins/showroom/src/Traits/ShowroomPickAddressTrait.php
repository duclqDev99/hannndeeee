<?php

namespace Botble\Showroom\Traits;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\District;
use Botble\Location\Models\Province;
use Botble\Location\Models\State;
use Botble\Location\Models\Ward;
use Botble\Showroom\Models\ShowroomPickAddress;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin \Eloquent
 */
trait ShowroomPickAddressTrait
{
    public function pickAddresses(): HasMany
    {
        return $this->hasMany(ShowroomPickAddress::class, 'showroom_code', 'code');
    }
}
