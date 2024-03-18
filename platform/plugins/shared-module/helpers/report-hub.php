<?php
// HUB

use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\ActualIssue;
use Botble\HubWarehouse\Models\ActualIssueDetail;
use Botble\HubWarehouse\Models\ActualReceipt;
use Botble\HubWarehouse\Models\ActualReceiptDetail;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Carbon\Carbon;

if(!function_exists('analysis_data_hub_by_date')){
    function analysis_data_hub_by_date($hub_id, $date = null) {
        $warehouses = Warehouse::query()
            ->where('status', HubStatusEnum::ACTIVE)
            ->when($hub_id && $hub_id != 0, function ($query) use ($hub_id) {
                $query->where('hub_id', $hub_id);
            })
            ->pluck('id')
            ->toArray();

        $countProductReceipt = countProductReceiptInStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $date);
        $countProductIssueInStatusApproved = countProductIssueHubInStatusApproved($warehouses, ProductIssueStatusEnum::APPOROVED, $date);
        $countProductWarehouse = getProductWarehouseHubExists($warehouses, $hub_id, Warehouse::class);
        return [
            'countProductReceipt' => $countProductReceipt,
            'countProductIssueInStatusApproved' => $countProductIssueInStatusApproved,
            'countProductWarehouse' => $countProductWarehouse
        ];

    }
}

function countProductIssueHubInStatusApproved($listWarehouse = [], $status ,$date){
    $issueIds = HubIssue::query()
            ->where('status', $status)
            ->whereIn('warehouse_issue_id', $listWarehouse)
            ->whereDate('created_at', '>=', $date['startDate'])
            ->whereDate('created_at', '<=', $date['endDate'])
            ->pluck('id')
            ->toArray();
    $actualIssueIds = ActualIssue::whereIn('hub_issue_id', $issueIds)->pluck('id');

    return ActualIssueDetail::query()
        ->select('quantity')
        ->whereIn('actual_id', $actualIssueIds)
        ->sum('quantity');
}

function getProductWarehouseHubExists($warehouses, $select1, $warehouseType) {
    return ProductQrcode::query()
        ->where('status', QRStatusEnum::INSTOCK)
        ->where('warehouse_type', $warehouseType)
        ->where('reference_type', Product::class)
        ->whereIn('warehouse_id', $warehouses)
        ->count();
}

function countProductReceiptInStatusApproved($listWarehouse = [], $status, $date){
    $receiptIds = getReceiptsHub($listWarehouse, $status, $date);

    $actualReceiptIds = ActualReceipt::whereIn('receipt_id', $receiptIds)->pluck('id')->toArray();

    return ActualReceiptDetail::query()
        ->select('quantity')
        ->whereIn('actual_id', $actualReceiptIds)
        ->sum('quantity');
}

 function getReceiptsHub($listWarehouse = [], $status, $date){
    $query = HubReceipt::query()->where('status', $status)
                    ->whereIn('warehouse_receipt_id', $listWarehouse)
                    ->whereDate('created_at', '>=', $date['startDate'])
                    ->whereDate('created_at', '<=', $date['endDate'])
                    ->pluck('id')
                    ->toArray();
    return $query;
}
// HUB

