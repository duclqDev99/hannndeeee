<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use Illuminate\Support\Facades\DB;

class TaxAmountCard extends Card
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

        $showroomId = null;
        $listShowroom = get_showroom_for_user()->pluck('name', 'id');
        if (count($listShowroom) > 0) {
            $showroomId = $listShowroom->keys()->first();
        }
        if (isset(request()->showroom_id)) {
            $showroomId = (int)request()->showroom_id;
        }
        if ($showroomId != null) {
            $showroomOrder = ShowroomOrder::query()
                ->where('where_id', $showroomId)
                ->where('where_type', Showroom::class)
                ->get()->pluck('order_id');

            $revenue = Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(tax_amount, 0)) as revenue'),
                    'payments.status',
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where('payments.status', PaymentStatusEnum::COMPLETED)
                ->whereIn('ec_orders.id', $showroomOrder)
                // ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
                ->whereDate('payments.created_at', '>=', $this->startDate)
                ->whereDate('payments.created_at', '<=', $this->endDate)
                ->groupBy('payments.status')
                ->first();
        } else {
            $revenue = 'Bạn không có quyền truy cập';
        }


        // $startDate = clone $this->startDate;
        // $endDate = clone $this->endDate;

        // $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        // $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        // $currentRevenue = null;
        // $previousRevenue = null;

        // $currentRevenue = Order::query()
        //         ->select([
        //             DB::raw('SUM(COALESCE(ec_orders.tax_amount, 0)) as revenue'),
        //             'payments.status',
        //         ])
        //         ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
        //         ->where('payments.status', PaymentStatusEnum::COMPLETED)
        //         ->whereIn('ec_orders.id', $showroomOrder)
        //         // ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
        //         ->whereDate('payments.created_at', '>=', $currentPeriod->getStartDate())
        //         ->whereDate('payments.created_at', '<=', $currentPeriod->getEndDate())
        //         ->groupBy('payments.status')
        //         ->pluck('revenue')
        //         ->first();

        // $previousRevenue = Order::query()
        //     ->select([
        //         DB::raw('SUM(COALESCE(ec_orders.tax_amount, 0)) as revenue'),
        //         'payments.status',
        //     ])
        //     ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
        //     ->where('payments.status', PaymentStatusEnum::COMPLETED)
        //     ->whereIn('ec_orders.id', $showroomOrder)
        //     // ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
        //     ->whereDate('payments.created_at', '>=', $previousPeriod->getStartDate())
        //     ->whereDate('payments.created_at', '<=', $previousPeriod->getEndDate())
        //     ->groupBy('payments.status')
        //     ->pluck('revenue')
        //     ->first();

        // $result = $currentRevenue - $previousRevenue;

        // $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        $result = 0;
        $this->chartColor = '#4ade80';


        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/showroom::reports.widgets.tax-amount-card',
                compact('revenue', 'result')
            )->render(),
        ]);
    }
}
