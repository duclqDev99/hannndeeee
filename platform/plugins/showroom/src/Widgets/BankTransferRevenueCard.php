<?php

namespace Botble\Showroom\Widgets;

use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Showroom\Repositories\Report\Interfaces\ReportRepositoryInterfaces;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class BankTransferRevenueCard extends Card
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
//        $data = Order::query()
//            ->whereDate('created_at', '>=', $this->startDate)
//            ->whereDate('created_at', '<=', $this->endDate)
//            ->select([
//                DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
//            ])
//            ->selectRaw('count(id) as total, date_format(created_at, "' . $this->dateFormat . '") as period')
//            ->groupBy('period')
//            ->pluck('revenue')
//            ->toArray();

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
                    ->where('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
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


        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;

        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create($startDate->subDays($currentPeriod->count()), $endDate->subDays($currentPeriod->count()));

        if (is_plugin_active('payment')) {
            $currentRevenue = Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                    'payments.status',
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->whereIn('payments.status', [PaymentStatusEnum::COMPLETED])
                ->whereIn('ec_orders.id', $showroomOrder)
                ->where('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
                ->whereDate('payments.created_at', '>=', $currentPeriod->getStartDate())
                ->whereDate('payments.created_at', '<=', $currentPeriod->getEndDate())
                ->groupBy('payments.status')
                ->pluck('revenue')
                ->first();

            $previousRevenue = Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                    'payments.status',
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->whereIn('payments.status', [PaymentStatusEnum::COMPLETED])
                ->whereIn('ec_orders.id', $showroomOrder)
                ->where('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
                ->whereDate('payments.created_at', '>=', $previousPeriod->getStartDate())
                ->whereDate('payments.created_at', '<=', $previousPeriod->getEndDate())
                ->groupBy('payments.status')
                ->pluck('revenue')
                ->first();
        } else {
            $currentRevenue = Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
                    'status',
                ])
                ->whereIn('id', $showroomOrder)
                ->where('status', OrderStatusEnum::COMPLETED)
                ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
                ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
                ->groupBy('status')
                ->pluck('revenue')
                ->first();
            $previousRevenue = Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
                    'status',
                ])
                ->whereIn('id', $showroomOrder)
                ->where('status', OrderStatusEnum::COMPLETED)
                ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
                ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
                ->groupBy('status')
                ->pluck('revenue')
                ->first();
        }

        // $result = $currentRevenue - $previousRevenue;
        $result = null;

        // $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/showroom::reports.widgets.bank-transfer-revenue-card',
                compact('revenue', 'result')
            )->render(),
        ]);
    }
}
