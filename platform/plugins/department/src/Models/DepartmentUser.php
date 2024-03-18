<?php

namespace Botble\Department\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class DepartmentUser extends User
{

    protected $fillable = [
        'first_name',
        'last_name',    
        'username',
        'password',
        'email',
        'phone',
        'first_name',
        'last_name',
        'department_code',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_code' , 'code',);
    }

    protected static function booted()
    {
        static::deleted(function ($departmentUser) {
            $departmentUser->roles && $departmentUser->roles()->sync([]);
        });
    }
}
