<?php

namespace Botble\ProcedureOrder\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ProcedureGroup extends BaseModel
{
    protected $table = 'procedure_groups';

    protected $fillable = [
        'name',
        'code',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'code' => SafeContent::class,
    ];
}
