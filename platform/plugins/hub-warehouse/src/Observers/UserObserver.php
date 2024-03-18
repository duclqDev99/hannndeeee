<?php


namespace Botble\HubWarehouse\Observers;

use Botble\ACL\Models\User;
use Botble\HubWarehouse\Services\StorageUserAgentService;
use Botble\HubWarehouse\Services\StorageUserDepartmentService;
use Botble\HubWarehouse\Services\StorageUserHubService;
use Botble\SaleWarehouse\Services\StorageUserSaleService;
use Botble\Showroom\Services\StorageUserShowroomService;
use Botble\WarehouseFinishedProducts\Services\StorageUserWarehouseService;


class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {

    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // ...
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // ...
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // ...
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // ...
    }
    public function saved(User $user)
    {
        if (\Auth::user()->hasPermission('users.edit-department')) {
            $data = request()->input();
            if (isset($data['last_name']) ) {
                $userHubService = new StorageUserHubService();
                $userAgentService = new StorageUserAgentService();
                $userWarehouseService = new StorageUserWarehouseService();
                $userDepartmentService = new StorageUserDepartmentService();
                $userShowRoomService = new StorageUserShowroomService();
                $userSaleService = new StorageUserSaleService();
                $hasHub = false;
                $hasAgent = false;
                $hasWarehouse = false;
                $hasShowRoom = false;
                $hasSale = false;
                if (isset($data['department_id'])) {
                    $userDepartmentService->execute(request(), $user);
                    foreach ($data['department_id'] as $deparment) {
                        if ($deparment[0] == 'retail_hub') {
                            $userHubService->execute(request(), $user);
                            $hasHub = true;
                        }
                        if ($deparment[0] == 'agent') {
                            $userAgentService->execute(request(), $user);
                            $hasAgent = true;
                        }
                        if ($deparment[0] == 'retail_warehouse_product') {
                            $userWarehouseService->execute(request(), $user);
                            $hasWarehouse = true;
                        }
                        if ($deparment[0] == 'show_room') {
                            $userShowRoomService->execute(request(), $user);
                            $hasShowRoom = true;
                        }
                        if ($deparment[0] == 'sale_warehouse') {
                            $userSaleService->execute(request(), $user);
                            $hasSale = true;
                        }
                    }
                } else {
                    $userDepartmentService->destroy(request(), $user);
                }
                if ($hasHub == false) {
                    $userHubService->destroy(request(), $user);
                }
                if ($hasAgent == false) {
                    $userAgentService->destroy(request(), $user);
                }
                if ($hasWarehouse == false) {
                    $userWarehouseService->destroy(request(), $user);
                }
                if ($hasShowRoom == false) {
                    $userShowRoomService->destroy(request(), $user);
                }
                if ($hasSale == false) {
                    $userSaleService->destroy(request(), $user);
                }
            }
        }
    }
}
