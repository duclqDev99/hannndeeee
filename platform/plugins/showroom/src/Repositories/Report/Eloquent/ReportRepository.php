<?php
namespace Botble\Showroom\Repositories\Report\Eloquent;

use Botble\Ecommerce\Models\Order;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Repositories\Report\Interfaces\ReportRepositoryInterfaces;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Support\Collection;

class ReportRepository extends RepositoriesAbstract implements ReportRepositoryInterfaces
{
    public function getFirstShowroomIdByUser($showroomId):int
    {
        $listShowroom = get_showroom_for_user()->pluck('name', 'id');
        if (!isset($showroomId)) {
                $showroomId = $listShowroom->keys()->first();
        }
        return $showroomId;
    }

    public function filterOrderInShowroomByUser($showroomId):array{
        $showroomOrder = ShowroomOrder::query()
            ->where('where_id', $showroomId)
            ->where('where_type', Showroom::class)
            ->get()->pluck('order_id')->toArray();
        return $showroomOrder;
    }
}
