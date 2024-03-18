<?php

namespace Botble\Showroom\Widgets;

use Botble\Agent\Widgets\Card;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Showroom\Repositories\Report\Interfaces\ReportRepositoryInterfaces;
use Illuminate\Support\Facades\DB;

class CashRevenueCard extends Card
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

            if (is_plugin_active('payment')) {
                $revenue = Order::query()
                    ->select([
                        DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                        'payments.status',
                    ])
                    ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                    ->whereIn('payments.status', [PaymentStatusEnum::COMPLETED])
                    ->whereIn('ec_orders.id', $showroomOrder)
                    ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
                    ->whereDate('payments.created_at', '>=', $this->startDate)
                    ->whereDate('payments.created_at', '<=', $this->endDate)
                    ->groupBy('payments.status')
                    ->first();
            } else {
                $revenue = Order::query()
                    ->select([
                        DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
                        'status',
                    ])
                    ->whereIn('id', $showroomOrder)
                    ->where('status', OrderStatusEnum::COMPLETED)
                    ->whereDate('created_at', '>=', $this->startDate)
                    ->whereDate('created_at', '<=', $this->endDate)
                    ->groupBy('status')
                    ->first();
            }
        } else {
            $revenue = 'Bạn không có quyền truy cập';
        }


        // $startDate = clone $this->startDate;
        // $endDate = clone $this->endDate;

        // $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        // $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        // $currentRevenue = null;
        // $previousRevenue = null;

        // if (is_plugin_active('payment')) {
        //     $currentRevenue = Order::query()
        //         ->select([
        //             DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
        //             'payments.status',
        //         ])
        //         ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
        //         ->whereIn('payments.status', [PaymentStatusEnum::COMPLETED])
        //         ->whereIn('ec_orders.id', $showroomOrder)
        //         ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
        //         ->whereDate('payments.created_at', '>=', $currentPeriod->getStartDate())
        //         ->whereDate('payments.created_at', '<=', $currentPeriod->getEndDate())
        //         ->groupBy('payments.status')
        //         ->pluck('revenue')
        //         ->first();

        //     $previousRevenue = Order::query()
        //         ->select([
        //             DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
        //             'payments.status',
        //         ])
        //         ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
        //         ->whereIn('payments.status', [PaymentStatusEnum::COMPLETED])
        //         ->whereIn('ec_orders.id', $showroomOrder)
        //         ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
        //         ->whereDate('payments.created_at', '>=', $previousPeriod->getStartDate())
        //         ->whereDate('payments.created_at', '<=', $previousPeriod->getEndDate())
        //         ->groupBy('payments.status')
        //         ->pluck('revenue')
        //         ->first();
        // } else {
        //     $currentRevenue = Order::query()
        //         ->select([
        //             DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
        //             'status',
        //         ])
        //         ->whereIn('id', $showroomOrder)
        //         ->where('status', OrderStatusEnum::COMPLETED)
        //         ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
        //         ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
        //         ->groupBy('status')
        //         ->pluck('revenue')
        //         ->first();
        //     $previousRevenue = Order::query()
        //         ->select([
        //             DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
        //             'status',
        //         ])
        //         ->whereIn('id', $showroomOrder)
        //         ->where('status', OrderStatusEnum::COMPLETED)
        //         ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
        //         ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
        //         ->groupBy('status')
        //         ->pluck('revenue')
        //         ->first();
        // }


        // $currentRevenue = Order::query()
        //     ->where('status', OrderStatusEnum::COMPLETED)
        //     ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
        //     ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
        //     ->select([
        //         DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
        //     ])
        //     ->pluck('revenue')
        //     ->toArray()[0];

        // $previousRevenue = Order::query()
        //     ->where('status', OrderStatusEnum::COMPLETED)
        //     ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
        //     ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
        //     ->select([
        //         DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
        //     ])
        //     ->pluck('revenue')
        //     ->toArray()[0];

        // $result = $currentRevenue - $previousRevenue;

        // $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        $result = 0;
        $this->chartColor = '#4ade80';


        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/showroom::reports.widgets.cash-revenue-card',
                compact('revenue', 'result')
            )->render(),
        ]);
    }
}
