<?php

namespace Botble\NotiAdminPusher\Models;

use Botble\ACL\Models\User;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\AdminNotificationQueryBuilder;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\BaseQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

class AdminNotification extends BaseModel
{
    use MassPrunable;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'title',
        'action_label',
        'action_url',
        'description',
        'permission',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'title' => SafeContent::class,
        'action_label' => SafeContent::class,
        'action_url' => SafeContent::class,
        'description' => SafeContent::class,
    ];

    public function markAsRead(): void
    {
        $this->update([
            'read_at' => Carbon::now(),
        ]);
    }

    public function prunable(): Builder|BaseQueryBuilder
    {
        return static::query()->where('created_at', '<=', Carbon::now()->subMonth());
    }

    public static function countUnread(): int
    {
        /**
         * @var NotificationAdminQueryBuilder $adminQuery
         */
        $adminQuery = AdminNotification::query();
        /**
         * @var Builder $query
         */
        $query = $adminQuery->hasPermission();
        $userId = request()->user()->id;
        return $query
            ->whereDoesntHave('userSeen',function($q) use($userId){
                $q->where('user_id', $userId);
            })
            ->select('action_url')
            ->count();
    }

    public function isAbleToAccess(): bool
    {
        $user = Auth::guard()->user();

        return ! $this->permission || $user->isSuperUser() || $user->hasPermission($this->permission);
    }

    protected static function booted(): void
    {
        static::creating(function (AdminNotification $notification) {
            if ($notification->action_url) {
                $notification->action_url = str_replace(url(''), '', $notification->action_url);
            }
        });
    }

    public function newEloquentBuilder($query): NotificationAdminQueryBuilder
    {
        return new NotificationAdminQueryBuilder($query);
    }
    public function userSeen()
    {
        return $this->belongsToMany(User::class, 'admin_notifications_seen')
                    ->withPivot('viewed')
                    ->withTimestamps();
    }
    public function isViewedByUser(User $user): bool
    {
        return $this->userSeen()->where('user_id', $user->id)->wherePivot('viewed', true)->exists();
    }
}
