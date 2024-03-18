<?php

namespace Botble\OverviewReport\Http\Controllers;

use ArchiElite\LogViewer\Log;
use Botble\Agent\Enums\AgentStatusEnum;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentIssue;
use Botble\Agent\Models\AgentReceipt;
use Botble\Agent\Models\AgentStatistics;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Botble\Base\Facades\Assets;
use Botble\Base\Widgets\Contracts\AdminWidget;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\ActualIssue;
use Botble\HubWarehouse\Models\ActualIssueDetail;
use Botble\HubWarehouse\Models\ActualReceipt;
use Botble\HubWarehouse\Models\ActualReceiptDetail;
use Botble\HubWarehouse\Models\HubActualReceiptBatch;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubIssueDetail;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\HubReceiptDetail;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Media\Facades\RvMedia;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomActualIssue;
use Botble\Showroom\Models\ShowroomActualIssueDetail;
use Botble\Showroom\Models\ShowroomActualReceipt;
use Botble\Showroom\Models\ShowroomActualReceiptDetail;
use Botble\Showroom\Models\ShowroomCustomer;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Showroom\Models\ShowroomIssueDetail;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Showroom\Models\ShowRoomReceiptDetail;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;

class OverviewReportController extends BaseController
{
    public function index(Request $request, AdminWidget $widget)
    {
        $this->pageTitle(trans('plugins/overview-report::overview-report.name'));

        Assets::addScriptsDirectly([
            'vendor/core/plugins/overview-report/js/report-component.js',
        ]);

        Assets::usingVueJS();

        [$startDate, $endDate] = EcommerceHelper::getDateRangeInReport($request);

        $listShowroomByUser = request()->user()->showroom->pluck('id');

        // $showroomList = get_showroom_for_user()->pluck('name', 'id');
        // $agentList = get_agent_for_user()->pluck('name', 'id');
        // $hubList = get_hub_for_user()->pluck('name', 'id');
        $showroomList = Showroom::query()->wherePublished()->get()->pluck('name', 'id');
        $agentList = Agent::query()->wherePublished()->get()->pluck('name', 'id');
        $hubList = HubWarehouse::query()->where('status', HubStatusEnum::ACTIVE)->get()->pluck('name', 'id');
        return view('plugins/overview-report::reports.overview', compact('startDate', 'endDate', 'widget', 'showroomList', 'agentList', 'hubList'));
    }

    public function getDataReportOfAgent(Request $request){
        $select1 = $request->selectOption1;
        $select2 = $request->selectOption2;
        $startDate = $this->convertUtcToLocalTime($request->startDate);
        $endDate = $this->convertUtcToLocalTime($request->endDate);

        $revenue = AgentStatistics::query()->where('where_type', Agent::class)
            ->when($select2 && $select2 != 0, function ($query) use ($select2) {
                return $query->where('where_id', $select2);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('revenue');

        $listWarehouse = AgentWarehouse::query()->where('status', AgentStatusEnum::ACTIVE)
            ->when($select2 && $select2 != 0, function ($query) use ($select2) {
                return $query->where('agent_id', $select2);
            })->pluck('id')->toArray();

        $warehouses = AgentWarehouse::query()
            ->where('status', AgentStatusEnum::ACTIVE)
            ->when($select2 && $select2 != 0, function ($query) use ($select2) {
                $query->where('agent_id', $select2);
            })
            ->select('id', 'name', 'status', 'description')->get();



        $totalProduct = $this->countProductsByStatus(QRStatusEnum::INSTOCK, $listWarehouse);

        $totalProductSold = $this->countProductsByStatus(QRStatusEnum::SOLD, $listWarehouse);

        $agentReceipts = $this->getReceipts($listWarehouse, ApprovedStatusEnum::PENDING, $startDate, $endDate, $select1);

        $agentIssues = $this->getIssues($listWarehouse, ProductIssueStatusEnum::PENDING, $startDate, $endDate, $select1);

        $countsAgentIssueInStatus = $this->countsIssueInStatus($select1, $listWarehouse, $startDate, $endDate);

        $countsReceiptsInStatus = $this->countsReceiptsInStatus($select1, $listWarehouse, $startDate, $endDate);

        $data = [
            'revenue'=> $revenue,
            'totalProduct'=> $totalProduct,
            'totalProductSold'=> $totalProductSold,
            'warehouses'=> $warehouses,
            'agentReceipts'=> $agentReceipts,
            'agentIssues'=> $agentIssues,
            'countsAgentIssueInStatus'=> $countsAgentIssueInStatus,
            'countsReceiptsInStatus'=> $countsReceiptsInStatus,
        ];
        return response()->json($data);
    }
    public function getDataReportOfShowroom(Request $request){
        $select1 = $request->selectOption1;
        $select2 = $request->selectOption2;
        $startDate = $this->convertUtcToLocalTime($request->startDate);
        $endDate = $this->convertUtcToLocalTime($request->endDate);

        $statusCompleted = is_plugin_active('payment') ? PaymentStatusEnum::COMPLETED : OrderStatusEnum::COMPLETED;

        $orderShowroom = ShowroomOrder::query()
            ->when($select2 && $select2 != 0, function ($query) use ($select2) {
                $query->where('where_id', $select2);
            })
            ->where('where_type', Showroom::class)
            ->get()->pluck('order_id');

        $warehouses = ShowroomWarehouse::query()
        ->where('status', ShowroomStatusEnum::ACTIVE)
        ->when($select2 && $select2 != 0, function ($query) use ($select2) {
            $query->where('showroom_id', $select2);
        })
        ->pluck('id')
        ->toArray();

        $listWarehouse = ShowroomWarehouse::query()
            ->where('status', ShowroomStatusEnum::ACTIVE)
            ->when($select2 && $select2 != 0, function ($query) use ($select2) {
                $query->where('showroom_id', $select2);
            })
            ->select('id', 'name', 'status', 'description')->get();

        $customerShowroom = ShowroomCustomer::query()
        ->when($select2 && $select2 != 0, function ($query) use ($select2) {
            $query->where('where_id', $select2);
        })
        ->where('where_type', Showroom::class)
        ->get()->pluck('customer_id');

        $revenue =  $this->getRevenue( $orderShowroom,$statusCompleted, $startDate, $endDate);

        $revenueBankTransfer =  $this->getBankTransferRevenue( $orderShowroom,$statusCompleted, $startDate, $endDate);

        $revenueCash =  $this->getCashRevenue( $orderShowroom,$statusCompleted, $startDate, $endDate);

        $taxAmount =  $this->getTaxAmount( $orderShowroom,$statusCompleted, $startDate, $endDate);

        $product =  $this->getProductWarehouseExists($warehouses, $select1, ShowroomWarehouse::class);

        $customer =  $this->getCustomeres($customerShowroom, $startDate, $endDate);

        $order =  $this->getOrders($orderShowroom, $startDate, $endDate);

        $getDataOrder =  $this->getDataOrder($orderShowroom, $startDate, $endDate);

        $showroomReceipts = $this->getReceipts($warehouses, ApprovedStatusEnum::PENDING, $startDate, $endDate, $select1);

        $showroomIssues = $this->getIssues($warehouses, ProductIssueStatusEnum::PENDING, $startDate, $endDate, $select1);

        $countsIssueInStatus = $this->countsIssueInStatus($select1, $warehouses, $startDate, $endDate);

        $countsReceiptsInStatus = $this->countsReceiptsInStatus($select1, $warehouses, $startDate, $endDate);

        $topSellingProducts = $this->getTopSellingProducts($warehouses, $startDate, $endDate, $select1);

        $countProductIssue = $this->countProductIssueInStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $startDate, $endDate, $select1);

        $countProductReceipt = $this->countProductReceiptInStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $startDate, $endDate, $select1);

        $countProductSold = $this->countProductSold($warehouses, $startDate, $endDate);

        $countProductPendingSold = $this->countProductPendingSold($warehouses, $startDate, $endDate);

        $totalrFundedPointAmount = $this->totalrFundedPointAmount($orderShowroom,$statusCompleted, $startDate, $endDate);

        $data = [
            'revenue'=> $revenue,
            'revenueBankTransfer'=> $revenueBankTransfer,
            'revenueCash'=> $revenueCash,
            'taxAmount'=> $taxAmount,
            'product'=> $product,
            'customer'=> $customer,
            'order'=> $order,
            'warehouses'=> $listWarehouse,
            'showroomReceipts'=> $showroomReceipts,
            'showroomIssues'=> $showroomIssues,
            'countsIssueInStatus'=> $countsIssueInStatus,
            'countsReceiptsInStatus'=> $countsReceiptsInStatus,
            'topSellingProducts'=> $topSellingProducts,
            'countProductIssue'=> $countProductIssue,
            'countProductReceipt'=> $countProductReceipt,
            'countProductSold'=> $countProductSold,
            'countProductPendingSold'=> $countProductPendingSold,
            'totalrFundedPointAmount'=> $totalrFundedPointAmount,
            'getDataOrder'=> $getDataOrder,

            // 'totalProduct'=> $totalProduct,
            // 'totalProductSold'=> $totalProductSold,
        ];
        return response()->json($data);
    }

    public function getDataReportOfHub(Request $request){
        $select1 = $request->selectOption1;
        $select2 = $request->selectOption2;
        $startDate = $this->convertUtcToLocalTime($request->startDate);
        $endDate = $this->convertUtcToLocalTime($request->endDate);

        $warehouses = Warehouse::query()
        ->where('status', HubStatusEnum::ACTIVE)
        ->when($select2 && $select2 != 0, function ($query) use ($select2) {
            $query->where('hub_id', $select2);
        })
        ->pluck('id')
        ->toArray();

        $listWarehouse = Warehouse::query()
        ->where('status', HubStatusEnum::ACTIVE)
        ->when($select2 && $select2 != 0, function ($query) use ($select2) {
            $query->where('hub_id', $select2);
        })
        ->select('id', 'name', 'status')->get();

        $hubReceipts = $this->getReceipts($warehouses, ApprovedStatusEnum::PENDING, $startDate, $endDate, $select1);
        $hubIssues = $this->getIssues($warehouses, ProductIssueStatusEnum::PENDING, $startDate, $endDate, $select1);

        $countsIssueInStatus = $this->countsIssueInStatus($select1, $warehouses, $startDate, $endDate);

        $countsReceiptsInStatus = $this->countsReceiptsInStatus($select1, $warehouses, $startDate, $endDate);

        $countProductIssue = $this->countProductIssueInStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $startDate, $endDate, $select1);

        $countProductReceipt = $this->countProductReceiptInStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $startDate, $endDate, $select1);

        $product =  $this->getProductWarehouseExists($warehouses, $select1, Warehouse::class);


        $data = [
            'warehouses'=> $listWarehouse,
            'hubReceipts'=> $hubReceipts,
            'hubIssues'=> $hubIssues,
            'countsIssueInStatus'=> $countsIssueInStatus,
            'countsReceiptsInStatus'=> $countsReceiptsInStatus,
            'countProductIssue'=> $countProductIssue,
            'countProductReceipt'=> $countProductReceipt,
            'product'=> $product,
            // 'totalProduct'=> $totalProduct,
            // 'totalProductSold'=> $totalProductSold,
        ];
        return response()->json($data);
    }

    private function convertUtcToLocalTime($utc){
        $utcTime = Carbon::parse($utc);
        return $utcTime->timezone('Asia/Ho_Chi_Minh');
    }
    private function getTopSellingProducts($warehouses, $startDate, $endDate, $select1){
        $res = [];
        switch ($select1) {
            case '1':
                break;
            case '2':
                $extractUniqueProductIds = ShowroomOrder::query()
                    ->where('where_type', Showroom::class)
                    ->whereIn('where_id', $warehouses)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->select()->get()->filter(function ($order) {
                        return $order->status === 'completed';
                    })->pluck('list_id_product_qrcode')->flatten();

                $productQrcode = ProductQrcode::query()
                            ->select([
                                DB::raw('COUNT(reference_id) as total'), // Đảm bảo rằng đây là cách bạn muốn tính 'total'
                                'reference_id',
                                'reference_type'
                            ])
                            ->whereIn('id', $extractUniqueProductIds)
                            ->with('reference')
                            ->groupBy('reference_id', 'reference_type') // Nhóm theo cả 'reference_id' và 'reference_type' nếu bạn muốn tính tổng cho mỗi loại reference
                            ->orderBy('total', 'desc') // Sắp xếp giảm dần dựa trên 'total'
                            ->limit(10)
                            ->get();
                $res = [];
                foreach ($productQrcode as $row) {
                    $attrProduct = [];

                    if ($row?->reference && $row?->reference?->variationProductAttributes) {
                        foreach($row?->reference->variationProductAttributes as $productAttributeSets){
                            if (isset($productAttributeSets->attribute_set_slug) && isset($productAttributeSets->title)) {
                                $attrProduct[$productAttributeSets->attribute_set_slug] = $productAttributeSets->title;
                            }
                        }
                    }
                    $valueConvert = $this->convertArrToString($attrProduct);
                    $urlImage = $row?->reference?->parentProduct->first()?->image;
                    $res[] = [
                        'name' => $row?->reference?->name . ' ' . $valueConvert,
                        'images' => !empty($urlImage) && $urlImage ? RvMedia::getImageUrl($urlImage, 'thumb', false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage(),
                        'total' => $row?->total,
                    ];
                }
                break;
            case '3':
                break;
            }

        return $res;

    }

    private function convertArrToString($value){
        $attrProductStrings = array_map(function ($key, $value) {
            return "{$key}:{$value}";
        }, array_keys($value), $value);

        return '(' . implode(' - ', $attrProductStrings) . ')';
    }

    private function extractUniqueProductIds($orderProductIds){
        // Gộp tất cả các ID vào một mảng duy nhất
        $allIds = array_reduce($orderProductIds, function ($carry, $item) {
            // Giải mã JSON để lấy mảng ID
            $ids = json_decode($item['list_id_product_qrcode'], true);
            // Gộp mảng ID vào mảng tổng
            return array_merge($carry, $ids);
        }, []);

        // Loại bỏ các ID trùng lặp
        $uniqueIds = array_unique($allIds);

        // Nếu bạn muốn reset các key của mảng (để chúng bắt đầu từ 0, 1, 2, ...)
        return array_values($uniqueIds);
    }



    private function countsReceiptsInStatus($select1,$listWarehouse, $startDate, $endDate){
        switch ($select1) {
            case '1':
                $statusCounts = AgentReceipt::query()
                    ->whereIn('warehouse_receipt_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get();
                $convertLabelStatus = [];
                foreach ($statusCounts as $item) {
                    $label = $item->status->label();
                    $convertLabelStatus[$label] = $item->total;
                }
                break;
            case '2':
                $statusCounts = ShowroomReceipt::query()
                    ->whereIn('warehouse_receipt_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get();
                $convertLabelStatus = [];
                foreach ($statusCounts as $item) {
                    $label = $item->status->label();
                    $convertLabelStatus[$label] = $item->total;
                }
                break;
            case '3':
                $statusCounts = HubReceipt::query()
                    ->whereIn('warehouse_receipt_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get();
                $convertLabelStatus = [];
                foreach ($statusCounts as $item) {
                    $label = $item->status->label();
                    $convertLabelStatus[$label] = $item->total;
                }
                break;
            }

        return $convertLabelStatus;
    }

    private function countsIssueInStatus($select1,$listWarehouse, $startDate, $endDate){
        switch ($select1) {
            case '1':
                $statusCounts = AgentIssue::query()
                    ->whereIn('warehouse_issue_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get();
                $convertLabelStatus = [];
                foreach ($statusCounts as $item) {
                    $label = ProductIssueStatusEnum::getLabel($item->status);
                    $convertLabelStatus[$label] = $item->total;
                }
                break;
            case '2':
                $statusCounts = ShowroomIssue::query()
                    ->whereIn('warehouse_issue_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get();
                $convertLabelStatus = [];
                foreach ($statusCounts as $item) {
                    $label = ProductIssueStatusEnum::getLabel($item->status);
                    $convertLabelStatus[$label] = $item->total;
                }
                break;
            case '3':
                $statusCounts = HubIssue::query()
                    ->whereIn('warehouse_issue_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->get();
                $convertLabelStatus = [];
                foreach ($statusCounts as $item) {
                    $label = ProductIssueStatusEnum::getLabel($item->status);
                    $convertLabelStatus[$label] = $item->total;
                }
                break;
            }
        return $convertLabelStatus;
    }

    private function countProductSold($listWarehouse = [], $startDate, $endDate){
        return ProductQrcode::query()
            ->where('status', QRStatusEnum::SOLD)
            ->where('warehouse_type', ShowroomWarehouse::class)
            ->where('reference_type', Product::class)
            ->whereIn('warehouse_id', $listWarehouse)
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate)
            ->count();
    }

    private function countProductPendingSold($listWarehouse = [], $startDate, $endDate){
        return ProductQrcode::query()
            ->where('status', QRStatusEnum::PENDINGSOLD)
            ->where('warehouse_type', ShowroomWarehouse::class)
            ->where('reference_type', Product::class)
            ->whereIn('warehouse_id', $listWarehouse)
            ->whereDate('updated_at', '>=', $startDate)
            ->whereDate('updated_at', '<=', $endDate)
            ->count();
    }

    private function countProductIssueInStatusApproved($listWarehouse = [], $status, $startDate, $endDate, $select1){
        $res = [];
        switch ($select1) {
            case '1':
                break;
            case '2':
                $issueIds = $this->getIssues($listWarehouse, $status, $startDate, $endDate, $select1);

                $actualIssueIds = ShowroomActualIssue::whereIn('showroom_issue_id', $issueIds)->pluck('id');

                return ShowroomActualIssueDetail::query()
                    ->select('quantity')
                    ->whereIn('actual_id', $actualIssueIds)
                    ->sum('quantity');
            case '3':
                $issueIds = $this->getIssues($listWarehouse, $status, $startDate, $endDate, $select1);
                $actualIssueIds = ActualIssue::whereIn('hub_issue_id', $issueIds)->pluck('id');

                return ActualIssueDetail::query()
                    ->select('quantity')
                    ->whereIn('actual_id', $actualIssueIds)
                    ->sum('quantity');
            }
        return $res;
    }



    private function getIssues($listWarehouse = [], $status, $startDate, $endDate, $select1){
        $res = [];
        switch ($select1) {
            case '1':
                $res = AgentIssue::query()
                    ->where('status', $status)
                    ->whereIn('warehouse_issue_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->select('id','title', 'status', 'invoice_issuer_name', 'expected_date', 'description')
                    ->get();
                break;
            case '2':
                $res = ShowroomIssue::query()
                    ->where('status', $status)
                    ->whereIn('warehouse_issue_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
                    if($status == 'approved'){
                        $res = $res->pluck('id')->toArray();
                    }else{
                        $res = $res->select('id','title', 'status', 'invoice_issuer_name', 'expected_date', 'description')
                            ->get();
                    }
                break;
            case '3':
                $res = HubIssue::query()
                    ->where('status', $status)
                    ->whereIn('warehouse_issue_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
                    if($status == 'approved'){
                        $res = $res->pluck('id')->toArray();
                    }else{
                        $res = $res->select('id','title', 'status', 'invoice_issuer_name', 'expected_date', 'description')
                                    ->get();
                    }
                break;
            }
            return $res;
    }
    private function countProductReceiptInStatusApproved($listWarehouse = [], $status, $startDate, $endDate, $select1){
        $res = 0;
        switch ($select1) {
            case '1':
                break;
            case '2':
                $receiptIds = $this->getReceipts($listWarehouse, $status, $startDate, $endDate, $select1);

                $actualReceiptIds = ShowroomActualReceipt::whereIn('receipt_id', $receiptIds)->pluck('id')->toArray();

                return ShowroomActualReceiptDetail::query()
                    ->select('quantity')
                    ->whereIn('actual_id', $actualReceiptIds)
                    ->sum('quantity');
            case '3':
                $receiptIds = $this->getReceipts($listWarehouse, $status, $startDate, $endDate, $select1);

                $actualReceiptIds = ActualReceipt::whereIn('receipt_id', $receiptIds)->pluck('id')->toArray();

                return ActualReceiptDetail::query()
                    ->select('quantity')
                    ->whereIn('actual_id', $actualReceiptIds)
                    ->sum('quantity');
            default:
                return $res = 0;
            }
        return $res;
    }

    private function getReceipts($listWarehouse = [], $status, $startDate, $endDate, $select1){
        try {
            switch ($select1) {
                case '1':
                    $model = AgentReceipt::query();
                    break;
                case '2':
                    $model = ShowRoomReceipt::query();
                    break;
                case '3':
                    $model = HubReceipt::query();
                    break;
                default:
                    return [];
            }
            $query = $model->where('status', $status)
                           ->whereIn('warehouse_receipt_id', $listWarehouse)
                           ->whereDate('created_at', '>=', $startDate)
                           ->whereDate('created_at', '<=', $endDate);

            if ($status == 'approved' && $select1 != '1') {
                $res = $query->pluck('id')->toArray();
            } else {
                $res = $query->select('id', 'title', 'status', 'invoice_issuer_name', 'expected_date', 'description')
                             ->get()
                             ->toArray();
            }
            return $res;
        } catch (\Exception $e) {
            FacadesLog::error(''. $e->getMessage());
            $res = [];
        }

        return $res;
    }

    private function countProductsByStatus($status, $listWarehouse) {
        return ProductQrcode::query()
            ->where('status', $status)
            ->where('warehouse_type', AgentWarehouse::class)
            ->where('reference_type', Product::class)
            ->whereIn('warehouse_id', $listWarehouse)
            ->count();
    }


    private function totalrFundedPointAmount($showroomOrder, $statusCompleted, $startDate, $endDate){
        if (is_plugin_active('payment')) {
            return Order::query()
                ->select([
                    DB::raw('SUM(payments.refunded_point_amount) as fundedPointAmount'),
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->whereIn('payments.status', [$statusCompleted])
                ->whereIn('ec_orders.id', $showroomOrder)
                ->groupBy('payments.status')
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->first();
        }
    }
    private function getRevenue($showroomOrder, $statusCompleted, $startDate, $endDate){
        if (is_plugin_active('payment')) {
            return Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                    'payments.status',
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->whereIn('payments.status', [$statusCompleted])
                ->whereIn('ec_orders.id', $showroomOrder)
                ->groupBy('payments.status')
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->first();
        } else {
            return Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(ec_orders.amount, 0)) as revenue'),
                    'status',
                ])
                ->whereIn('id', $showroomOrder)
                ->where('status', $statusCompleted)
                ->groupBy('status')
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->first();
        }
    }

    private function getBankTransferRevenue($showroomOrder, $statusCompleted, $startDate, $endDate){
        if (is_plugin_active('payment')) {
            return Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                    'payments.status',
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where('payments.status', $statusCompleted)
                ->whereIn('ec_orders.id', $showroomOrder)
                ->where('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->groupBy('payments.status')
                ->first();
        } else {
            return Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
                    'status',
                ])
                ->whereIn('id', $showroomOrder)
                ->where('status', $statusCompleted)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->groupBy('status')
                ->first();
        }
    }

    private function getCashRevenue($showroomOrder, $statusCompleted, $startDate, $endDate){
        if (is_plugin_active('payment')) {
            return Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
                    'payments.status',
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where('payments.status', $statusCompleted)
                ->whereIn('ec_orders.id', $showroomOrder)
                ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->groupBy('payments.status')
                ->first();
        } else {
            return Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(amount, 0)) as revenue'),
                    'status',
                ])
                ->whereIn('id', $showroomOrder)
                ->where('status', $statusCompleted)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->groupBy('status')
                ->first();
        }
    }

    private function getTaxAmount($showroomOrder, $statusCompleted, $startDate, $endDate){
        return Order::query()
                ->select([
                    DB::raw('SUM(COALESCE(tax_amount, 0)) as revenue'),
                    'payments.status',
                ])
                ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where('payments.status', $statusCompleted)
                ->whereIn('ec_orders.id', $showroomOrder)
                // ->whereNot('payment_channel', PaymentMethodEnum::BANK_TRANSFER)
                ->whereDate('payments.created_at', '>=', $startDate)
                ->whereDate('payments.created_at', '<=', $endDate)
                ->groupBy('payments.status')
                ->first();
    }

    private function getProductWarehouseExists($warehouses, $select1, $warehouseType) {
        return ProductQrcode::query()
            ->where('status', QRStatusEnum::INSTOCK)
            ->where('warehouse_type', $warehouseType)
            ->where('reference_type', Product::class)
            ->whereIn('warehouse_id', $warehouses)
            ->count();
    }

    private function getCustomeres($customeres, $startDate, $endDate){
        return Customer::query()
        // ->groupBy('period')
        ->selectRaw('count(id) as total')
        ->whereDate('created_at', '>=', $startDate)
        ->whereDate('created_at', '<=', $endDate)
        ->whereIn('id', $customeres)
        ->pluck('total')
        ->all();
    }

    private function getOrders($showroomOrder, $startDate, $endDate){
        return Order::query()
            ->selectRaw('count(id) as total')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereIn('id', $showroomOrder)
            ->pluck('total')
            ->all();
    }

    private function getDataOrder($showroomOrder, $startDate, $endDate){
        return Order::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereIn('id', $showroomOrder)
            ->get();
    }
}
