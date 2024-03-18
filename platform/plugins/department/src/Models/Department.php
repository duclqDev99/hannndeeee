<?php

namespace Botble\Department\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class Department extends BaseModel
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function members(): HasMany
    {
        return $this->hasMany(DepartmentUser::class, 'department_code' , 'code');
    }

    protected static function booted()
    {
        static::deleted(function ($department) {
            $department->members && $department->members->each->delete();
        });
    }

}
