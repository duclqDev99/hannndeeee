<?php

namespace Botble\Showroom\Widgets;

use Botble\Agent\Widgets\Card;
use Botble\Ecommerce\Models\Order;
use Botble\Showroom\Repositories\Report\Interfaces\ReportRepositoryInterfaces;

class NewOrderCard extends Card
{

    protected $reportRepository;

    public function __construct(ReportRepositoryInterfaces $reportRepository)
    {
        parent::__construct();
        $this->reportRepository = $reportRepository;
    }

    public function getOptions(): array
    {
        $data = [];

        return [
            'series' => [
                [
                    'data' => $data,
                ],
            ],
        ];
    }

    public function getViewData(): array
    {
        $showroomId = $this->reportRepository->getFirstShowroomIdByUser(request()->showroom_id);

        if ($showroomId != null) {
            $showroomOrder = $this->reportRepository->filterOrderInShowroomByUser($showroomId);

            $count = Order::query()
                ->whereIn('id', $showroomOrder)
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->count();
        } else {
            $count = 'Bạn không có quyền truy cập';
        }


//         $startDate = clone $this->startDate;
//         $endDate = clone $this->endDate;
//
//         $currentPeriod = CarbonPeriod::create($startDate, $endDate);
//         $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));
//
//         $currentOrders = Order::query()
//             ->whereIn('id', $showroomOrder)
//             ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
//             ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
//             ->count();
//
//         $previousOrders = Order::query()
//             ->whereIn('id', $showroomOrder)
//             ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
//             ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
//             ->count();
//
//         $result = $currentOrders - $previousOrders;
        $result = 0;


        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/showroom::reports.widgets.new-order-card',
                compact('count', 'result')
            )->render(),
        ]);
    }
}
