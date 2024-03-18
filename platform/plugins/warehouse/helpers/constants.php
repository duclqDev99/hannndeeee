<?php

use Botble\Agent\Models\AgentActualIssue;
use Botble\Agent\Models\AgentActualReceipt;
use Botble\Agent\Models\AgentIssue;
use Botble\Agent\Models\AgentOrder;
use Botble\Agent\Models\AgentReceipt;
use Botble\Agent\Models\AngentProposalIssue;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\ActualIssue;
use Botble\HubWarehouse\Models\ActualReceipt as HubWarehouseModelsActualReceipt;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\OrderHgf\Noti\HGFNoti;
use Botble\OrderRetail\Noti\RetailNoti;
use Botble\OrderStepSetting\Models\Action;
use Botble\Sales\Models\Order;
use Botble\Showroom\Models\ExchangeGoods;
use Botble\Showroom\Models\ShowroomActualIssue;
use Botble\Showroom\Models\ShowroomActualReceipt;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Models\ShowroomProposalIssue;
use Botble\Showroom\Models\ShowroomProposalReceipt;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Warehouse\Models\ActualReceipt;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialOutConfirm;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Warehouse\Models\ProposalPurchaseGoods;
use Botble\Warehouse\Models\ReceiptPurchaseGoods;
use Botble\WarehouseFinishedProducts\Models\ActualReceipt as ModelsActualReceipt;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;

if (!defined('WAREHOUSE_MODULE_SCREEN_NAME')) {
    define('WAREHOUSE_MODULE_SCREEN_NAME', 'warehouse');
}

if (!defined('CATEGORY_MODULE_SCREEN_NAME')) {
    define('CATEGORY_MODULE_SCREEN_NAME', 'product-categories');
}

if (!defined('PRODUCT_MODULE_SCREEN_NAME')) {
    define('PRODUCT_MODULE_SCREEN_NAME', 'finished-product');
}

if (!defined('BRANCH_MODULE_SCREEN_NAME')) {
    define('BRANCH_MODULE_SCREEN_NAME', 'finished-branch');
}

if (!defined('PROPOSAL_MODULE_SCREEN_NAME')) {
    define('PROPOSAL_MODULE_SCREEN_NAME', 'proposal');
}

if (!defined('RECEIPT_MODULE_SCREEN_NAME')) {
    define('RECEIPT_MODULE_SCREEN_NAME', 'receipt');
}

if (!defined('STOCK_MODULE_SCREEN_NAME')) {
    define('STOCK_MODULE_SCREEN_NAME', 'stock');
}

if (!defined('AGENCY_MODULE_SCREEN_NAME')) {
    define('AGENCY_MODULE_SCREEN_NAME', 'agency');
}

if (!defined('DELIVERY_MODULE_SCREEN_NAME')) {
    define('DELIVERY_MODULE_SCREEN_NAME', 'delivery');
}

if (!defined('ACCPEIPT_DELIVERY_MODULE_SCREEN_NAME')) {
    define('ACCPEIPT_DELIVERY_MODULE_SCREEN_NAME', 'acceipt-delivery');
}


if (!defined('MATERIAL_MODULE_SCREEN_NAME')) {
    define('MATERIAL_MODULE_SCREEN_NAME', 'material');
}

if (!defined('TYPE_MATERIAL_MODULE_SCREEN_NAME')) {
    define('TYPE_MATERIAL_MODULE_SCREEN_NAME', 'type_material');
}

if (!defined('IMPORT_EXPORT_MATERIAL_MODULE_SCREEN_NAME')) {
    define('IMPORT_EXPORT_MATERIAL_MODULE_SCREEN_NAME', 'import_export_material');
}

if (!defined('MATERIAL_PLAN_MODULE_SCREEN_NAME')) {
    define('MATERIAL_PLAN_MODULE_SCREEN_NAME', 'material_plan');
}

if (!defined('PROCESSING_HOUSE_MODULE_SCREEN_NAME')) {
    define('PROCESSING_HOUSE_MODULE_SCREEN_NAME', 'processing_house');
}

if (!defined('SUPPLIER_MODULE_SCREEN_NAME')) {
    define('SUPPLIER_MODULE_SCREEN_NAME', 'supplier');
}

if (!defined('INVENTORY_MATERIAL_MODULE_SCREEN_NAME')) {
    define('INVENTORY_MATERIAL_MODULE_SCREEN_NAME', 'inventory_material');
}

if (!defined('CHECK_INVENTORY_MODULE_SCREEN_NAME')) {
    define('CHECK_INVENTORY_MODULE_SCREEN_NAME', 'check_inventory');
}

if (!defined('RECEIPT_INVENTORY_MODULE_SCREEN_NAME')) {
    define('RECEIPT_INVENTORY_MODULE_SCREEN_NAME', 'receipt-inventory');
}

if (!defined('AGENCY_MODULE_SCREEN_NAME')) {
    define('AGENCY_MODULE_SCREEN_NAME', 'agency');
}

if (!defined('DELIVERY_MODULE_SCREEN_NAME')) {
    define('DELIVERY_MODULE_SCREEN_NAME', 'delivery');
}

if (!defined('MTPROPOSAL_MODULE_SCREEN_NAME')) {
    define('MTPROPOSAL_MODULE_SCREEN_NAME', 'mtproposal');
}

if (!defined('MATERIAL_PROPOSAL_PURCHASE_MODULE_SCREEN_NAME')) {
    define('MATERIAL_PROPOSAL_PURCHASE_MODULE_SCREEN_NAME', 'material-proposal');
}

if (!defined('MATERIAL_RECEIPT_PURCHASE_MODULE_SCREEN_NAME')) {
    define('MATERIAL_RECEIPT_PURCHASE_MODULE_SCREEN_NAME', 'material-receipt');
}

if (!defined('MATERIAL_PROPOSAL_OUT_MODULE_SCREEN_NAME')) {
    define('MATERIAL_PROPOSAL_OUT_MODULE_SCREEN_NAME', 'material-proposal-out');
}

if (!defined('MATERIAL_RECEIPT_OUT_MODULE_SCREEN_NAME')) {
    define('MATERIAL_RECEIPT_OUT_MODULE_SCREEN_NAME', 'material-receipt-out');
}

if (!defined('ACTUAL_RECEIPT_MODULE_SCREEN_NAME')) {
    define('ACTUAL_RECEIPT_MODULE_SCREEN_NAME', 'actual_receipt');
}
if (!defined('PROPOSAL_PURCHASE_GOODS_MODULE_SCREEN_NAME')) {
    define('PROPOSAL_PURCHASE_GOODS_MODULE_SCREEN_NAME', 'proposal-purchase-goods');
}

if (!defined('RECEIPT_PURCHASE_GOODS_MODULE_SCREEN_NAME')) {
    define('RECEIPT_PURCHASE_GOODS_MODULE_SCREEN_NAME', 'receipt-purchase-goods');
}
if (!defined('ACTUALOUT_MODULE_SCREEN_NAME')) {
    define('ACTUALOUT_MODULE_SCREEN_NAME', 'actualout');
}

if (!defined('LIST_TELEGRAM_CHAT_ID')) {
    $listChatID = [
        [
            setting('tele_chat_id_receipt_issue_material', '') => [
                MaterialProposalPurchase::class,
                MaterialReceiptConfirm::class,
                ProposalPurchaseGoods::class,
                ReceiptPurchaseGoods::class,
                MaterialOut::class,
                ModelsActualReceipt::class,
                MaterialOutConfirm::class,
            ]
        ], [
            setting('tele_chat_id_receipt_issue', '') => [
                ProposalReceiptProducts::class,
                ModelsActualReceipt::class,
                ProposalProductIssue::class,
                Order::class,
                ProductIssue::class,
                ReceiptProduct::class,

                //Đại lý
                ProposalAgentReceipt::class,
                AgentReceipt::class,
                AngentProposalIssue::class,
                AgentIssue::class,
                AgentActualIssue::class,
                AgentActualReceipt::class,
                //HUB
                ProposalHubReceipt::class,
                HubReceipt::class,
                ProposalHubIssue::class,
                HubIssue::class,
                HubWarehouseModelsActualReceipt::class,
                ActualIssue::class,
                //Showroom
                ShowroomProposalReceipt::class,
                ShowRoomReceipt::class,
                ShowroomProposalIssue::class,
                ShowroomIssue::class,
                ShowroomActualReceipt::class,
                ShowroomActualIssue::class,
            ]
        ], [
            setting('tele_chat_id_order_purchase', '') => [
                AgentOrder::class,
                ShowroomOrder::class,
                ExchangeGoods::class,
            ]
        ], [
            setting('tele_chat_id_order_hgf', '') => [
                HGFNoti::class
            ]
        ], [
            setting('tele_chat_id_order_retail', '') => [
                RetailNoti::class
            ]
        ],
        [
            setting('tele_chat_id_ec_product', '') => [
                Product::class,
            ]
        ],
    ];
    define('LIST_TELEGRAM_CHAT_ID', $listChatID);
}
