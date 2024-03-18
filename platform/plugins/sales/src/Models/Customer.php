<?php

namespace Botble\Sales\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Customer extends BaseModel
{
    protected $table = 'hd_customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'email',
        'phone',
        'address',
        'dob',
        'level',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'first_name' => SafeContent::class,
        'last_name' => SafeContent::class,
        'gender' => SafeContent::class,
        'email' => SafeContent::class,
        'address' => SafeContent::class,
    ];

    
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst((string)$value),
            set: fn ($value) => ucfirst((string)$value),
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst((string)$value),
            set: fn ($value) => ucfirst((string)$value),
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->last_name . ' ' . $this->first_name,
        );
    }

}
