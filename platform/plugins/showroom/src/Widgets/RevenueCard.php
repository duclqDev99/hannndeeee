<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use Illuminate\Support\Facades\DB;

class RevenueCard extends Card
{
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
        $showroomOrder = $this->getListShowroomOrderId();
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $statusCompleted = is_plugin_active('payment') ? PaymentStatusEnum::COMPLETED : OrderStatusEnum::COMPLETED;


        $revenueIsPluginPayment = $this->getRevenueIsPluginPayment(
            $showroomOrder,
            $statusCompleted
        );
        $revenueIsPlugin = $this->getRevenueIsPlugin(
            $showroomOrder,
            $statusCompleted
        );

        if (is_plugin_active('payment')) {
            $revenue = $revenueIsPluginPayment
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->first();
        } else {
            $revenue = $revenueIsPlugin
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->first();
        }

        // $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        // $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        // if (is_plugin_active('payment')) {
        //     $currentRevenue = $revenueIsPluginPayment
        //                 ->whereDate('payments.created_at', '>=', $currentPeriod->getStartDate())
        //                 ->whereDate('payments.created_at', '<=', $currentPeriod->getEndDate())
        //                 ->pluck('revenue')
        //                 ->first();

        //     $previousRevenue = $revenueIsPluginPayment
        //                 ->whereDate('payments.created_at', '>=', $previousPeriod->getStartDate())
        //                 ->whereDate('payments.created_at', '<=', $previousPeriod->getEndDate())
        //                 ->pluck('revenue')
        //                 ->first();
        // } else {
        //     $currentRevenue = $revenueIsPlugin
        //                 ->whereDate('payments.created_at', '>=', $currentPeriod->getStartDate())
        //                 ->whereDate('payments.created_at', '<=', $currentPeriod->getEndDate())
        //                 ->pluck('revenue')
        //                 ->first();
        //     $previousRevenue = $revenueIsPlugin
        //                 ->whereDate('payments.created_at', '>=', $previousPeriod->getStartDate())
        //                 ->whereDate('payments.created_at', '<=', $previousPeriod->getEndDate())
        //                 ->pluck('revenue')
        //                 ->first();
        // }

        // $result = $currentRevenue - $previousRevenue;
        $result = 0;
        $this->chartColor = '#4ade80';

        // $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';


        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/showroom::reports.widgets.revenue-card',
                compact('revenue', 'result')
            )->render(),
        ]);
    }

    private function getRevenueData($startDate, $endDate)
    {
        return Order::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select([
                DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
            ])
            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
            ->groupBy('period')
            ->pluck('revenue')
            ->toArray();
    }

    private function getListShowroomOrderId()
    {
        $listShowroom = get_showroom_for_user()->pluck('name', 'id');
        $showroom_id = request()->showroom_id;
        if (count($listShowroom) > 0) {
            $showroomId = $listShowroom->keys()->first();
        }
        if (isset($showroom_id)) {
            $showroomId = (int)$showroom_id;
        }
        if ($showroomId != null) {
            return ShowroomOrder::query()
                ->where('where_id', $showroomId)
                ->where('where_type', Showroom::class)
                ->get()->pluck('order_id');
        }
        return null;
    }

    private function getRevenueIsPluginPayment($showroomOrder, $statusCompleted)
    {
        return Order::query()
            ->select([
                DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                'payments.status',
            ])
            ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->whereIn('payments.status', [$statusCompleted])
            ->whereIn('ec_orders.id', $showroomOrder)
            ->groupBy('payments.status');
    }

    private function getRevenueIsPlugin($showroomOrder, $statusCompleted)
    {
        return Order::query()
            ->select([
                DB::raw('SUM(COALESCE(ec_orders.amount, 0)) as revenue'),
                'status',
            ])
            ->whereIn('id', $showroomOrder)
            ->where('status', $statusCompleted)
            ->groupBy('status');
    }
}
