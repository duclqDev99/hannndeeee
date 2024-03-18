<?php

namespace Botble\OrderRetail\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Customer extends BaseModel
{
    protected $table = 'retail_customers';
   
    protected $fillable = [
        'name',
        'phone',
        'email',
        'dob',
        'level',
    ];

    protected $casts = [
        'name' => SafeContent::class,
        'phone' => SafeContent::class,
        'email' => SafeContent::class,
        'dob' => SafeContent::class,
    ];
}
