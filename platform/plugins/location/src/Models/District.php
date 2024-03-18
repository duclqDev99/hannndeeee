<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends BaseModel
{
    protected $table = 'viettel_district';

    protected $fillable = [
        'viettel_id',
        'viettel_value',
        'viettel_name',
        'viettel_provice_id',
        'date_created',
        'date_update',
        'region',
    ];
}
