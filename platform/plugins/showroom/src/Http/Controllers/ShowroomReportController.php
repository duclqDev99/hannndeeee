<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Widgets\Contracts\AdminWidget;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Tables\Reports\TopSellingProductsTable;
use Botble\Ecommerce\Tables\Reports\TrendingProductsTable;
use Botble\Showroom\Tables\Reports\RecentOrdersTable;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShowroomReportController extends BaseController
{

    private $pageTitle;

    public function __construct()
    {
        $this->pageTitle = trans('plugins/showroom::showroom.page_title');
    }

    public function getIndex(Request $request, AdminWidget $widget)
    {
        $this->pageTitle($this->pageTitle['reports']);

        Assets::addScriptsDirectly([
            'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.js',
            'vendor/core/plugins/showroom/js/report.js',
        ])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.css',
                'vendor/core/plugins/ecommerce/css/report.css',
            ]);

        Assets::usingVueJS();

        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport($request);

        $listShowroomByUser = request()->user()->showroom->pluck('id');

        $showroomList = get_showroom_for_user()->pluck('name', 'id');

        if ($showroomList->isEmpty()) {
            return redirect()->route('dashboard.index');
        }
        // $showroomList = Showroom::where('status','published')
        //     ->whereIn('id', $listShowroomByUser)
        //     ->pluck('name', 'id')
        //     ->all();
        $showroom_id = count($listShowroomByUser) > 0 ? $listShowroomByUser[0] : 0;


        if ($request->ajax()) {
            return $this
                ->httpResponse()->setData(view('plugins/showroom::reports.ajax', compact('widget'))->render());
        }
        // dd($widget);
        return view(
            'plugins/showroom::reports.index',
            compact('startDate', 'endDate', 'widget', 'showroomList', 'showroom_id')
        );
    }

    public function getTopSellingProducts(TopSellingProductsTable $topSellingProductsTable)
    {
        return $topSellingProductsTable->renderTable();
    }

    public function getRecentOrders(RecentOrdersTable $recentOrdersTable)
    {
        return $recentOrdersTable->renderTable();
    }

    public function getTrendingProducts(TrendingProductsTable $trendingProductsTable)
    {
        return $trendingProductsTable->renderTable();
    }

    public function getDashboardWidgetGeneral()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now();

        $processingOrders = Order::query()
            ->where('status', OrderStatusEnum::PENDING)
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $completedOrders = Order::query()
            ->where('status', OrderStatusEnum::COMPLETED)
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $today)
            ->count();

        $revenue = Order::countRevenueByDateRange($startOfMonth, $today);

        $lowStockProducts = Product::query()
            ->where('with_storehouse_management', 1)
            ->where('quantity', '<', 2)
            ->where('quantity', '>', 0)
            ->count();

        $outOfStockProducts = Product::query()
            ->where('with_storehouse_management', 1)
            ->where('quantity', '<', 1)
            ->count();

        return $this
            ->httpResponse()
            ->setData(
                view(
                    'plugins/agent::reports.widgets.general',
                    compact(
                        'processingOrders',
                        'revenue',
                        'completedOrders',
                        'outOfStockProducts',
                        'lowStockProducts'
                    )
                )->render()
            );
    }
}
