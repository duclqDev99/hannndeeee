<?php
namespace Botble\NotiAdminPusher\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\NotiAdminPusher\Models\AdminNotification;
use Botble\NotiAdminPusher\Models\NotificationAdminQueryBuilder;

class NotificationController extends BaseController
{
    public function index(): BaseHttpResponse
    {
        $notificationsCount = AdminNotification::countUnread();
        /** 
        * @var NotificationAdminQueryBuilder $adminQuery
        */

        $adminQuery = AdminNotification::query();
        $query = $adminQuery->hasPermission();

        $notifications = $query
            ->latest()
            ->paginate(10);

        return $this
            ->httpResponse()
            ->setData(view('plugins/noti-admin-pusher::notification.partials.content', compact('notifications', 'notificationsCount'))->render());
    }

    public function destroy(int|string $id): BaseHttpResponse
    {
        // $notificationItem = AdminNotification::query()->findOrFail($id);
        // $notificationItem->delete();

        // /**
        //  * @var AdminNotificationQueryBuilder $adminQuery
        //  */
        // $adminQuery = AdminNotification::query();

        // /**
        //  * @var Builder $query
        //  */
        // $query = $adminQuery->hasPermission();

        // if (! $query->exists()) {
        //     return $this
        //         ->httpResponse()
        //         ->setData(view('core/base::notification.partials.content')->render());
        // }

        // return $this->httpResponse();
    }

    public function deleteAll()
    {
        // AdminNotification::query()->delete();

        // return $this->httpResponse();
    }

    public function read(int|string $id)
    {
        /**
         * @var AdminNotification $notificationItem
         */
        $notificationItem = AdminNotification::query()->findOrFail($id);
        $userId = request()->user()->id;
        $notificationItem->userSeen()->syncWithoutDetaching([$userId => ['viewed' => true]]);

        if (! $notificationItem->action_url || $notificationItem->action_url == '#') {
            return redirect()->back();
        }

        return redirect()->to(url($notificationItem->action_url));
    }

    public function readAll()
    {
        $notifications = AdminNotification::query()->hasPermission()->pluck('id')->toArray();
        $user = request()->user();
        $user->notifications()->syncWithoutDetaching(array_fill_keys($notifications, ['viewed' => true]));


        return $this->httpResponse();
    }

    public function countUnread(): BaseHttpResponse
    {
        return $this
            ->httpResponse()
            ->setData(AdminNotification::countUnread());
    }
}
