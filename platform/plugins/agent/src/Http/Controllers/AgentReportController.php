<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentOrder;
use Botble\Agent\Models\AgentProduct;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Widgets\Contracts\AdminWidget;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Agent\Tables\Reports\RecentOrdersTable;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Tables\Reports\TopSellingProductsTable;
use Botble\Agent\Tables\Reports\TrendingProductsTable;
use Botble\ProductQrcode\Helpers\random;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Botble\Agent\Http\Requests\AgentOrderExportRequest;
use Botble\Agent\Exports\ReportExport;
use Maatwebsite\Excel\Excel;


class AgentReportController extends BaseController
{

    protected $excel;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }
    public function getIndex(Request $request, AdminWidget $widget, BaseHttpResponse $response)
    {
        $this->pageTitle('Báo cáo thống kê');

        Assets::addScriptsDirectly([
            'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.js',
            'vendor/core/plugins/agent/js/report.js',
        ])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.css',
                'vendor/core/plugins/ecommerce/css/report.css',
            ]);

        Assets::usingVueJS();

        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport($request);

        $user = request()->user();
        // $agentList = $user->isSuperUser() ?

        $agentList = Agent::where('status', 'published')
                    ->when(!$user->isSuperUser(), function($query) use ($user) {
                        $query->whereIn('id', $user->agent->pluck('id'));
                    })
                    ->pluck('name', 'id')
                    ->all();
        if(!count($agentList) > 0){
            return $response
                ->setPreviousUrl(route('agent.index'))
                ->setError()
                ->setMessage(trans('Bạn không thuộc đại lý này!!!'));
        }
        $agent_id = key($agentList);

        if ($request->ajax()) {
            return $this
                ->httpResponse()->setData(view('plugins/agent::reports.ajax', compact('widget'))->render());
        }
        return view(
            'plugins/agent::reports.index',
            compact('startDate', 'endDate', 'widget', 'agentList', 'agent_id')
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

    public function exportReport(AgentOrderExportRequest $request)
    {
        $data = $request->input();

        $startDate = new Carbon(request()?->start_date);
        $endDate = new Carbon(request()?->end_date);
        $agentId = (int)request()->agent_id;

        $agentList = Agent::query()->wherePublished()->select('name')->pluck('name')->toArray();

        $exportAction = new ReportExport($agentList);
        dd($agentList);

//        return  $this->excel->download($exportAction, 'report-agent' . '.xlsx');
//        $agenProduct = AgentProduct::query()
//            ->where('where_id', $agentId)
//            ->where('where_type', Agent::class)
//            ->whereDate('created_at', '>=', $startDate)
//            ->whereDate('created_at', '<=', $endDate)
//            ->get()->pluck('product_id');
//        $product = Product::query()
//            ->whereIn('id', $agenProduct)
//            ->where('is_variation', 1)
//            ->wherePublished()->get();
//        dd($agentList);
    }

}
