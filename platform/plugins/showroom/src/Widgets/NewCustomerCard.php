<?php

namespace Botble\Showroom\Widgets;

use Botble\Agent\Widgets\Card;
use Botble\Ecommerce\Models\Customer;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomCustomer;
use Botble\Showroom\Repositories\Report\Interfaces\ReportRepositoryInterfaces;


class NewCustomerCard extends Card
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
            $agenCustomer = ShowroomCustomer::query()
                ->where('where_id', $showroomId)
                ->where('where_type', Showroom::class)
                ->get()->pluck('customer_id');
            $count = Customer::query()
                ->whereIn('id', $agenCustomer)
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->count();
        } else {
            $count = 'Bạn không có quyền truy cập';
        }

        // $startDate = clone $this->startDate;
        // $endDate = clone $this->endDate;

        // $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        // $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        // $currentCustomers = Customer::query()
        //     ->whereIn('id', $agenCustomer)
        //     ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
        //     ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
        //     ->count();

        // $previousCustomers = Customer::query()
        //     ->whereIn('id', $agenCustomer)
        //     ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
        //     ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
        //     ->count();

        // $result = $currentCustomers - $previousCustomers;

        $result = 0;
        $this->chartColor = '#4ade80';


        // $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';


        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/showroom::reports.widgets.new-customer-card',
                compact('count', 'result')
            )->render(),
        ]);
    }
}
