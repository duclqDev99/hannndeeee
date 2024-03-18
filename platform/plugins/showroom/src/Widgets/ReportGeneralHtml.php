<?php

namespace Botble\Showroom\Widgets;

use Botble\Base\Widgets\Html;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ReportGeneralHtml extends Html
{
    public function getContent(): string
    {
        if (!is_plugin_active('payment')) {
            return '';
        }
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
        }

        $count = [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $revenues = Order::query()
            ->select([
                DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                'payments.status',
            ])
            ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->whereIn('ec_orders.id', $showroomOrder)
            ->whereIn('payments.status', [PaymentStatusEnum::COMPLETED, PaymentStatusEnum::PENDING])
            ->whereDate('payments.created_at', '>=', $count['startDate'])
            ->whereDate('payments.created_at', '<=', $count['endDate'])
            ->groupBy('payments.status')
            ->get();
        $revenueCompleted = $revenues->firstWhere('status', PaymentStatusEnum::COMPLETED);
        $revenuePending = $revenues->firstWhere('status', PaymentStatusEnum::PENDING);
        $count['revenues'] = [
            [
                'label' => PaymentStatusEnum::COMPLETED()->label(),
                'value' => $revenueCompleted ? (int)$revenueCompleted->revenue : 0,
                'status' => true,
                'color' => '#80bc00',
            ],
            [
                'label' => PaymentStatusEnum::PENDING()->label(),
                'value' => $revenuePending ? (int)$revenuePending->revenue : 0,
                'status' => false,
                'color' => '#E91E63',
            ],
        ];

        // Order::getRevenueData($this->startDate, $this->endDate)
        $revenues = Order::query()
            ->select(
                'payments.refunded_amount',
                'payments.amount',
                'payments.created_at'
            )
            ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->whereIn('ec_orders.id', $showroomOrder)
            ->where('payments.status', PaymentStatusEnum::COMPLETED)
            ->whereDate('payments.created_at', '>=', $this->startDate)
            ->whereDate('payments.created_at', '<=', $this->endDate)
            ->get();

        $series = [];
        $dates = [];
        $earningSales = collect();
        $period = CarbonPeriod::create($this->startDate->startOfDay(), $this->endDate->endOfDay());

        $colors = ['#fcb800', '#80bc00'];

        $data = [
            'name' => get_application_currency()->title,
            'data' => [],
        ];
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $filtered = $revenues->filter(function ($item) use ($formattedDate) {
                return $item->created_at->format('Y-m-d') == $formattedDate;
            });
            $sum = $filtered->sum('amount') - $filtered->sum('refunded_amount');

            $data['data'][] = (float)$sum;
        }

        $earningSales[] = [
            'text' => trans('plugins/showroom::reports.items_earning_sales', [
                'value' => format_price(collect($data['data'])->sum()),
            ]),
            'color' => Arr::get($colors, $earningSales->count(), Arr::first($colors)),
        ];

        $series[] = $data;

        foreach ($period as $date) {
            $dates[] = $date->toDateString();
        }

        $colors = $earningSales->pluck('color');

        $salesReport = compact('dates', 'series', 'earningSales', 'colors');

        $revenues = fn(string $key): array => collect($count['revenues'])->pluck($key)->toArray();

        return view('plugins/showroom::reports.widgets.revenues', compact('count', 'salesReport', 'revenues'))->render();
    }
}
