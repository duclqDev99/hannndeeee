<?php

namespace Botble\Location\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Province extends BaseModel
{
    use HasSlug;

    protected $table = 'viettel_province';

    protected $fillable = [
        'viettel_code',
        'viettel_id',
        'viettel_name',
        'date_created',
        'date_update',
    ];
}
