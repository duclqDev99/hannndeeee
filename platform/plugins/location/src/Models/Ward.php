<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ward extends BaseModel
{
    use HasSlug;

    protected $table = 'viettel_wards';

    protected $fillable = [
        'viettel_id',
        'viettel_name',
        'viettel_district_id',
    ];

}
