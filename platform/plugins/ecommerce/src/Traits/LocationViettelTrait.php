<?php

namespace Botble\Ecommerce\Traits;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\District;
use Botble\Location\Models\Province;
use Botble\Location\Models\State;
use Botble\Location\Models\Ward;
use Botble\Showroom\Models\Showroom;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Eloquent
 */
trait LocationViettelTrait
{
   
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'viettel_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'viettel_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id', 'viettel_id');
    }

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(Showroom::class, 'showroom_id');
    }

    public function getProvinceNameAttribute(): string|null
    {
        $value = $this->province->viettel_name;
        return $value;
    }


    public function getDistrictNameAttribute(): string|null
    {
        $value = $this->district->viettel_name;
        return $value;
    }

    public function getWardNameAttribute(): string|null
    {
        $value = $this->ward->viettel_name;
        return $value;
    }

    public function viettelFullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', array_filter([
                $this->address,
                $this->province_name,
                $this->district_name,
                $this->ward_name,
                EcommerceHelper::isZipCodeEnabled() ? $this->zip_code : '',
            ])),
        );
    }
}
