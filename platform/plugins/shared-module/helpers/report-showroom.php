<?php

//Showroom

use Botble\Agent\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\ActualIssue;
use Botble\HubWarehouse\Models\ActualIssueDetail;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomActualReceipt;
use Botble\Showroom\Models\ShowroomActualReceiptDetail;
use Botble\Showroom\Models\ShowroomCustomer;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

if(!function_exists('analysis_data_showroom_by_date')){
    function analysis_data_showroom_by_date($showroom_id, $date = null) {

        $statusCompleted = is_plugin_active('payment') ? PaymentStatusEnum::COMPLETED : OrderStatusEnum::COMPLETED;

        $orderShowroom = ShowroomOrder::query()
            ->when($showroom_id && $showroom_id != 0, function ($query) use ($showroom_id) {
                $query->where('where_id', $showroom_id);
            })
            ->where('where_type', Showroom::class)
            ->get()->pluck('order_id');

        $warehouses = ShowroomWarehouse::query()
        ->where('status', ShowroomStatusEnum::ACTIVE)
        ->when($showroom_id && $showroom_id != 0, function ($query) use ($showroom_id) {
            $query->where('showroom_id', $showroom_id);
        })
        ->pluck('id')
        ->toArray();



        $customerShowroom = ShowroomCustomer::query()
        ->when($showroom_id && $showroom_id != 0, function ($query) use ($showroom_id) {
            $query->where('where_id', $showroom_id);
        })
        ->where('where_type', Showroom::class)
        ->get()->pluck('customer_id');

        $revenue =  getRevenue( $orderShowroom,$statusCompleted, $date);

        $revenueBankTransfer =  getBankTransferRevenue( $orderShowroom,$statusCompleted, $date);

        $revenueCash = getCashRevenue( $orderShowroom,$statusCompleted, $date);

        $taxAmount =  getTaxAmount( $orderShowroom,$statusCompleted, $date);

        $countProductReceipt = countProductReceiptInShowroomStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $date);

        $countProductIssue = countProductIssueShowroomInStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $date);

        $countProductSold = countProductSold($warehouses, $date);

        $countProductPendingSold = countProductPendingSold($warehouses, $date);

        $test =  getProductWarehouseShowroomExists($warehouses, ShowroomWarehouse::class);

        $customer = getCustomeres($customerShowroom, $date);

        $order = getOrders($orderShowroom, $date);

        $product = dailyInventoryTotals($warehouses, $date);

        $totalrFundedPointAmount = totalrFundedPointAmount($orderShowroom,$statusCompleted, $date);

        return [
            'revenue'=> $revenue ? $revenue->revenue : 0,
            'revenueBankTransfer'=> $revenueBankTransfer ? $revenueBankTransfer->revenue : 0,
            'revenueCash'=> $revenueCash ? $revenueCash->revenue : 0,
            'taxAmount'=> $taxAmount ? $taxAmount->revenue : 0,
            'product'=> $product,
            'customer'=> $customer,
            'order'=> $order,
            'countProductIssue'=> $countProductIssue,
            'countProductReceipt'=> $countProductReceipt,
            'countProductSold'=> $countProductSold,
            'countProductPendingSold'=> $countProductPendingSold,
            'totalrFundedPointAmount'=> $totalrFundedPointAmount ? $totalrFundedPointAmount->fundedPointAmount : 0,
        ];
    }
}

function totalrFundedPointAmount($showroomOrder, $statusCompleted, $date){
    if (is_plugin_active('payment')) {
        return Order::query()
            ->select([
                DB::raw('SUM(payments.refunded_point_amount) as fundedPointAmount'),
            ])
            ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->whereIn('payments.status', [$statusCompleted])
            ->whereIn('ec_orders.id', $showroomOrder)
            ->groupBy('payments.status')
            ->whereDate('payments.created_at', '>=', $date['startDate'])
            ->whereDate('payments.created_at', '<=', $date['endDate'])
            ->first();
    }
}

function countProductPendingSold($listWarehouse = [], $date){
    return ProductQrcode::query()
        ->where('status', QRStatusEnum::PENDINGSOLD)
        ->where('warehouse_type', ShowroomWarehouse::class)
        ->where('reference_type', Product::class)
        ->whereIn('warehouse_id', $listWarehouse)
        ->whereDate('updated_at', '>=', $date['startDate'])
        ->whereDate('updated_at', '<=', $date['endDate'])
        ->count();
}

function countProductSold($listWarehouse = [], $date){
    return ProductQrcode::query()
        ->where('status', QRStatusEnum::SOLD)
        ->where('warehouse_type', ShowroomWarehouse::class)
        ->where('reference_type', Product::class)
        ->whereIn('warehouse_id', $listWarehouse)
        ->whereDate('updated_at', '>=', $date['startDate'])
        ->whereDate('updated_at', '<=', $date['endDate'])
        ->count();
}

function countProductReceiptInShowroomStatusApproved($listWarehouse = [], $status, $date){
    $receiptIds = getReceiptsShowroom($listWarehouse, $status, $date);

    $actualReceiptIds = ShowroomActualReceipt::whereIn('receipt_id', $receiptIds)->pluck('id')->toArray();

    return ShowroomActualReceiptDetail::query()
        ->select('quantity')
        ->whereIn('actual_id', $actualReceiptIds)
        ->sum('quantity');
}
function countProductIssueShowroomInStatusApproved($listWarehouse = [], $status, $date){
    $issueIds = getIssues($listWarehouse, $status, $date);
    $actualIssueIds = ActualIssue::whereIn('hub_issue_id', $issueIds)->pluck('id');

    return ActualIssueDetail::query()
        ->select('quantity')
        ->whereIn('actual_id', $actualIssueIds)
        ->sum('quantity');
}

function getIssues($listWarehouse = [], $status, $date){
    return ShowroomIssue::query()
                ->where('status', $status)
                ->whereIn('warehouse_issue_id', $listWarehouse)
                ->whereDate('created_at', '>=', $date['startDate'])
                ->whereDate('created_at', '<=', $date['endDate'])
                ->pluck('id')->toArray();
}

function getReceiptsShowroom($listWarehouse = [], $status, $date){
        return ShowRoomReceipt::query()->where('status', $status)
                       ->whereIn('warehouse_receipt_id', $listWarehouse)
                       ->whereDate('created_at', '>=', $date['startDate'])
                       ->whereDate('created_at', '<=', $date['endDate'])
                       ->pluck('id')->toArray();

}


function getOrders($showroomOrder, $date){
    return Order::query()
        ->selectRaw('count(id) as total')
        ->whereDate('created_at', '>=', $date['startDate'])
        ->whereDate('created_at', '<=', $date['endDate'])
        ->whereIn('id', $showroomOrder)
        ->pluck('total')
        ->all();
}

function getRevenue($showroomOrder, $statusCompleted, $date){
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
            ->whereDate('payments.updated_at', '>=', $date['startDate'])
            ->whereDate('payments.updated_at', '<=', $date['endDate'])
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
            ->whereDate('payments.created_at', '>=', $date['startDate'])
            ->whereDate('payments.created_at', '<=', $date['endDate'])
            ->first();
    }
}

function getBankTransferRevenue($showroomOrder, $statusCompleted, $date){
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
            ->whereDate('payments.updated_at', '>=', $date['startDate'])
            ->whereDate('payments.updated_at', '<=', $date['endDate'])
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
            ->whereDate('payments.created_at', '>=', $date['startDate'])
            ->whereDate('payments.created_at', '<=', $date['endDate'])
            ->groupBy('status')
            ->first();
    }
}

function getCashRevenue($showroomOrder, $statusCompleted, $date){
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
            ->whereDate('payments.updated_at', '>=', $date['startDate'])
            ->whereDate('payments.updated_at', '<=', $date['endDate'])
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
            ->whereDate('payments.created_at', '>=', $date['startDate'])
            ->whereDate('payments.created_at', '<=', $date['endDate'])
            ->groupBy('status')
            ->first();
    }
}

function getTaxAmount($showroomOrder, $statusCompleted, $date){
    return Order::query()
            ->select([
                DB::raw('SUM(COALESCE(tax_amount, 0)) as revenue'),
                'payments.status',
            ])
            ->join('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->where('payments.status', $statusCompleted)
            ->whereIn('ec_orders.id', $showroomOrder)
            ->whereDate('payments.created_at', '>=', $date['startDate'])
            ->whereDate('payments.created_at', '<=', $date['endDate'])
            ->groupBy('payments.status')
            ->first();
}

function getProductWarehouseShowroomExists($warehouses, $warehouseType) {
    return ProductQrcode::query()
        ->where('status', QRStatusEnum::INSTOCK)
        ->where('warehouse_type', $warehouseType)
        ->where('reference_type', Product::class)
        ->whereIn('warehouse_id', $warehouses)
        ->count();
}

function getCustomeres($customeres, $date){
    return Customer::query()
    // ->groupBy('period')
    ->selectRaw('count(id) as total')
    ->whereDate('created_at', '>=', $date['startDate'])
    ->whereDate('created_at', '<=', $date['endDate'])
    ->whereIn('id', $customeres)
    ->pluck('total')
    ->all();
}
function dailyInventoryTotals($listWarehouse, $date){
    //sản phẩm bán
    $countProductSold = ProductQrcode::query()
        ->where('status', QRStatusEnum::SOLD)
        ->where('warehouse_type', ShowroomWarehouse::class)
        ->where('reference_type', Product::class)
        ->whereIn('warehouse_id', $listWarehouse)
        ->whereDate('updated_at', '<=', $date['endDate'])
        ->count();

    // phẩm chờ bán
    $countProductPendingSold = ProductQrcode::query()
        ->where('status', QRStatusEnum::PENDINGSOLD)
        ->where('warehouse_type', ShowroomWarehouse::class)
        ->where('reference_type', Product::class)
        ->whereIn('warehouse_id', $listWarehouse)
        ->whereDate('updated_at', '<=', $date['endDate'])
        ->count();
    //tổng sản phẩm nhập
    $receiptIds = ShowRoomReceipt::query()->where('status', ProductIssueStatusEnum::APPOROVED)
                        ->whereIn('warehouse_receipt_id', $listWarehouse)
                        ->whereDate('created_at', '<=', $date['endDate'])
                        ->pluck('id')->toArray();

    $actualReceiptIds = ShowroomActualReceipt::whereIn('receipt_id', $receiptIds)->pluck('id')->toArray();

    $totalRecept = ShowroomActualReceiptDetail::query()
        ->select('quantity')
        ->whereIn('actual_id', $actualReceiptIds)
        ->sum('quantity');

    // tổng sản phẩm xuất
    $issueIds = ShowroomIssue::query()
        ->where('status', ProductIssueStatusEnum::APPOROVED)
        ->whereIn('warehouse_issue_id', $listWarehouse)
        ->whereDate('created_at', '<=', $date['endDate'])
        ->pluck('id')->toArray();

    $actualIssueIds = ActualIssue::whereIn('hub_issue_id', $issueIds)->pluck('id');

    $totalIssue = ActualIssueDetail::query()
        ->select('quantity')
        ->whereIn('actual_id', $actualIssueIds)
        ->sum('quantity');

    return (int)$totalRecept - (int)$totalIssue - (int)$countProductSold - (int)$countProductPendingSold;
}
//Showroom
