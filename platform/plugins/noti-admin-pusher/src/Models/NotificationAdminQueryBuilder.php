<?php

namespace Botble\NotiAdminPusher\Models;

use Botble\Base\Models\BaseQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NotificationAdminQueryBuilder extends BaseQueryBuilder
{
    public function hasPermission(): self
    {
        $user = Auth::guard()->user();

        if ($user->isSuperUser()) {
            return $this;
        }
        $this->when($user->permissions, function ($query, $permissions) {
            $query->where(function ($query) use ($permissions) {
                /**
                 * @var Builder $query
                 */
                $query
                    ->whereNull('permission')
                    ->orWhereIn('permission', array_keys($permissions));
            });
        });

        return $this;
    }
}
