<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Botble\Agent\Enums\ProposalAgentEnum;
use Botble\Agent\Models\AgentReceipt;
use Botble\Agent\Models\AgentReceiptDetail;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Http\Requests\HubIssueRequest;
use Botble\HubWarehouse\Models\ActualIssue;
use Botble\HubWarehouse\Models\ActualIssueDetail;
use Botble\HubWarehouse\Models\ActualIssueQrCode;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\HubReceiptDetail;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Models\ShowroomProposalReceipt;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Showroom\Models\ShowRoomReceiptDetail;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\WarehouseFinishedProducts\Enums\BatchDetailStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProductQrHistotry;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Botble\WarehouseFinishedProducts\Models\ReceiptProductDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\HubWarehouse\Tables\HubIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\HubWarehouse\Forms\HubIssueForm;
use Botble\Base\Forms\FormBuilder;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\IssueInputTour;
use Botble\HubWarehouse\Repositories\Interfaces\HubIssueRepositoryInterface;
use Botble\HubWarehouse\Supports\HubIssueHelper;
use Botble\SaleWarehouse\Models\SaleReceipt;
use Botble\SaleWarehouse\Models\SaleReceiptDetail;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Str;

class HubIssueController extends BaseController
{

    // private $preXK = 'XK';
    // private $preNK = 'NK';

    protected $hubIssueRepository;

    public function __construct(HubIssueRepositoryInterface $hubIssueRepository)
    {
        $this->hubIssueRepository = $hubIssueRepository;
    }
    public function index(HubIssueTable $table)
    {
        PageTitle::setTitle(trans('Danh sách phiếu'));

        return $table->renderTable();
    }



    public function store(HubIssueRequest $request, BaseHttpResponse $response)
    {
        $hubIssue = HubIssue::query()->create($request->input());

        event(new CreatedContentEvent(HUB_ISSUE_MODULE_SCREEN_NAME, $request, $hubIssue));

        return $response
            ->setPreviousUrl(route('hub-issue.index'))
            ->setNextUrl(route('hub-issue.edit', $hubIssue->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }


    public function edit(HubIssue $hubIssue, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $hubIssue->name]));

        return $formBuilder->create(HubIssueForm::class, ['model' => $hubIssue])->renderForm();
    }

    public function update(HubIssue $hubIssue, HubIssueRequest $request, BaseHttpResponse $response)
    {
        $hubIssue->fill($request->input());

        $hubIssue->save();

        event(new UpdatedContentEvent(HUB_ISSUE_MODULE_SCREEN_NAME, $request, $hubIssue));

        return $response
            ->setPreviousUrl(route('hub-issue.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(HubIssue $hubIssue, Request $request, BaseHttpResponse $response)
    {
        try {
            $hubIssue->delete();

            event(new DeletedContentEvent(HUB_ISSUE_MODULE_SCREEN_NAME, $request, $hubIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function viewConfirm($id, BaseHttpResponse $response)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/gallery/js/gallery-admin.js',
            // 'https://code.jquery.com/jquery-3.5.1.slim.min.js',
            'https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js',
            'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'
        ]);
        PageTitle::setTitle('Phiếu thực xuất kho');
        $productIssue = HubIssue::where('id', $id)->first();
        $productIssue = $this->applyWarehouseFilter($productIssue, $response);
        if (!$productIssue) {
            return $response
                ->setError()
                ->setMessage(__('Không có quyền truy cập'));
        }
        if ($productIssue->status->toValue() != ProductIssueStatusEnum::PENDING && $productIssue->status->toValue() != ProductIssueStatusEnum::PENDINGISSUE) {
            return $response
                ->setError()
                ->setMessage(__('Không có quyền truy cập'));
        }
        return view('plugins/hub-warehouse::hub-issue.confirm', compact('productIssue'));
    }
    public function confirm(HubIssue $productIssue, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        $productIssue = HubIssue::where('id', $productIssue->id)->sharedLock()->first();
        foreach ($productIssue->productIssueDetail as $detail) {
            if ($detail->quantity_scan < $detail->quantity)
                 {
                return $response
                    ->setError()
                    ->setMessage('Quét chưa đủ số lượng đề xuất vui lòng quét lại');
            }
        }

        abort_if(check_user_depent_of_hub($productIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $requestData = $request->input();
        if ($productIssue->status == ProductIssueStatusEnum::PENDINGISSUE) {
            try {
                $proposal = $this->getProposal($productIssue);
                $productIssue->proposal->update(['status' => ProposalIssueStatusEnum::CONFIRM]);
                $this->updateHubIssue($productIssue);
                $processBatch = $this->processBatchIds($requestData, $response, $proposal);
                if (!$processBatch) {
                    return $this->responseError($response, 'Lỗi khi xuất lô');
                }
                $processQRcode = $this->processQrIds($requestData, $proposal);
                if (!$processQRcode) {
                    return $this->responseError($response, 'Lỗi khi xuất lẻ');
                }
                if (isset($proposal?->is_warehouse) && $proposal?->is_warehouse != 6) {
                    $receipt = $this->handleReceiptCreation($productIssue, $proposal, $requestData);
                } else {
                    $receipt = null;
                }
                $actualIssue = $this->createActualIssue($productIssue, $requestData);
                $this->handleBatchProducts($requestData, $productIssue, $receipt, $actualIssue);
                $this->handleProducts($requestData, $productIssue, $receipt, $actualIssue);
                DB::commit();
                return $response
                    ->setPreviousUrl(route('hub-issue.view', $productIssue->id))
                    ->setNextUrl(route('hub-issue.view', $productIssue->id))
                    ->setMessage(trans('Thành công'));
            } catch (Exception $e) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($e->getMessage());
            }
        } else {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage('Đơn đã được xác nhận');
        }
    }
    private function handleBatchProducts($requestData, $productIssue, $receipt = null, $actualIssue)
    {
        if (isset($requestData['batch_ids'])) {
            foreach ($requestData['batch_ids'] as $batch_id) {
                $batch = ProductBatch::find((int) $batch_id);
                if ($batch) {
                    $color = '';
                    $size = '';
                    $product = '';
                    if ($receipt) {
                        $this->createReceiptDetail($receipt, $batch, $product, $color, $size, null);
                    }
                    $this->createActualIssueDetail($actualIssue, $batch, $product);
                }
            }
        }
    }
    private function handleProducts($requestData, $productIssue, $receipt = null, $actualIssue)
    {
        if (isset($requestData['qr_ids'])) {
            foreach ($requestData['qr_ids'] as $qrId) {
                $qrCode = ProductQrcode::find($qrId);
                $product = Product::find($qrCode?->reference_id);
                list($color, $size) = $this->extractColorAndSize($product->variationProductAttributes);
                if ($receipt) {
                    $this->createReceiptDetail($receipt, null, $product, $color, $size, $qrId);
                }
                $this->createActualIssueDetail($actualIssue, null, $product);
            }

        }

    }
    private function createReceiptDetail($receipt, $batch, $product, $color, $size, $qrId)
    {
        if ($batch !== null) {
            $data = [
                'batch_id' => $batch->id,
                'quantity' => $batch->quantity,
                'qrcode_id' => $batch->getQRCode->id,

            ];
        }
        if ($qrId != null) {
            $data = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'sku' => $product->sku,
                'color' => $color,
                'size' => $size,
                'qrcode_id' => $qrId,
                'quantity' => 1
            ];
        }
        if ($receipt instanceof HubReceipt) {
            $data['hub_receipt_id'] = $receipt->id;
            HubReceiptDetail::query()->create($data);
        } elseif ($receipt instanceof AgentReceipt) {
            $data['agent_receipt_id'] = $receipt->id;
            AgentReceiptDetail::query()->create($data);
        } elseif ($receipt instanceof ShowRoomReceipt) {
            $data['showroom_receipt_id'] = $receipt->id;
            ShowRoomReceiptDetail::query()->create($data);
        } elseif ($receipt instanceof SaleReceipt) {
            $data['sale_receipt_id'] = $receipt->id;
            SaleReceiptDetail::query()->create($data);
        }
        // Add additional conditions for other types of receipts if necessary
    }
    private function createActualIssue($productIssue, $requestData)
    {
        $filteredImages = array_filter($requestData['images']);
        $imageJson = json_encode($filteredImages);
        // Create the ActualIssue with the filtered and imploded images
        return ActualIssue::query()->create([
            'hub_issue_id' => $productIssue->id,
            'image' => $imageJson,
        ]);
    }
    private function handleReceiptCreation($productIssue, $proposal, $requestData)
    {
        $receiptCode = $this->generateReceiptCode($productIssue);
        return $this->createReceipt($productIssue, $proposal, $requestData, $receiptCode);
    }
    private function generateReceiptCode($productIssue)
    {
        $warehouseType = $productIssue->warehouse_type;
        $receiptModel = $warehouseType == AgentWarehouse::class
            ? AgentReceipt::class : ($warehouseType == Warehouse::class
                ? HubReceipt::class : ($warehouseType == ShowroomWarehouse::class
                    ? ShowRoomReceipt::class : SaleReceipt::class));
        $lastReceipt = $receiptModel::orderByDesc('id')->first();
        return $lastReceipt ? (int) $lastReceipt->receipt_code + 1 : 1;
    }
    private function createReceipt($productIssue, $proposal, $requestData, $receiptCode)
    {
        $warehouseType = $productIssue->warehouse_type;
        if ($productIssue->from_proposal_receipt == 1) {
            $data = $this->prepareReceiptDataFromProposal($productIssue, $proposal, $receiptCode);
            if ($warehouseType == AgentWarehouse::class) {
                $receipt = AgentReceipt::query()->create($data);
                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "agent-receipt.confirm",
                    'route' => route('agent-receipt.confirmView', $receipt->id),
                    'status' => 'tạo'
                ];
                send_notify_cms_and_tele($receipt, $arrNoti);
            } else if ($warehouseType == Warehouse::class) {
                $receipt = HubReceipt::query()->create($data);
                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "hub-receipt.confirm",
                    'route' => route('hub-receipt.confirm', $receipt->id),
                    'status' => 'tạo'
                ];
                send_notify_cms_and_tele($receipt, $arrNoti);
            } else if ($warehouseType == ShowroomWarehouse::class) {
                $receipt = ShowroomReceipt::query()->create($data);
                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "showroom-receipt.confirm",
                    'route' => route('showroom-receipt.confirmView', $receipt->id),
                    'status' => 'tạo'
                ];
                send_notify_cms_and_tele($receipt, $arrNoti);
            }
        } else {
            $baseData = $this->prepareBaseReceiptData($productIssue, $proposal);
            if ($proposal->is_warehouse == 1 || $proposal->is_warehouse == 2) {
                $specificData = $this->prepareSpecificReceiptData($proposal, $receiptCode);
                $receipt = HubReceipt::query()->create(array_merge($baseData, $specificData));
                $arrNoti = [
                    'action' => 'tạo',
                    'permission' => "agent-receipt.confirm",
                    'route' => route('agent-receipt.confirmView', $receipt->id),
                    'status' => 'tạo'
                ];
                send_notify_cms_and_tele($receipt, $arrNoti);
            } else if ($proposal->is_warehouse == 3) {
                $lastReceiptProduct = ReceiptProduct::orderByDesc('id')->first();
                $newReceiptCode = $lastReceiptProduct ? (int) $lastReceiptProduct->proposal_code + 1 : 1;
                $specificData = $this->prepareSpecificReceiptDataForProduct($proposal, $newReceiptCode);
                $receipt = ReceiptProduct::query()->create(array_merge($baseData, $specificData));
            } else if ($proposal->is_warehouse == 6) {
                $lastReceiptProduct = ReceiptProduct::orderByDesc('id')->first();
                $newReceiptCode = $lastReceiptProduct ? (int) $lastReceiptProduct->proposal_code + 1 : 1;
                $specificData = $this->prepareSpecificReceiptDataForProduct($proposal, $newReceiptCode);
                $receipt = ReceiptProduct::query()->create(array_merge($baseData, $specificData));
            } else {
                if ($warehouseType == AgentWarehouse::class) {
                    $receipt = AgentReceipt::query()->create(array_merge($baseData, [
                        'from_hub_warehouse' => 1,
                        'warehouse_receipt_id' => $proposal->warehouse_id,
                        'warehouse_name' => $proposal->warehouse->name,
                        'warehouse_id' => $proposal->warehouse_issue_id,
                        'quantity' => $proposal->quantity,
                        'warehouse_type' => Warehouse::class,
                        'receipt_code' => $receiptCode,
                    ]));
                    $arrNoti = [
                        'action' => 'tạo',
                        'permission' => "agent-receipt.confirm",
                        'route' => route('agent-receipt.confirmView', $receipt->id),
                        'status' => 'tạo'
                    ];
                    send_notify_cms_and_tele($receipt, $arrNoti);
                } else if ($warehouseType == ShowroomWarehouse::class) {
                    $receipt = ShowRoomReceipt::query()->create(array_merge($baseData, [
                        'from_hub_warehouse' => 1,
                        'warehouse_receipt_id' => $proposal->warehouse_id,
                        'warehouse_name' => $proposal->warehouse->name,
                        'warehouse_id' => $proposal->warehouse_issue_id,
                        'warehouse_type' => Warehouse::class,
                        'receipt_code' => $receiptCode,
                    ]));
                    $arrNoti = [
                        'action' => 'tạo',
                        'permission' => "showroom-receipt.confirm",
                        'route' => route('showroom-receipt.confirmView', $receipt->id),
                        'status' => 'tạo'
                    ];
                    send_notify_cms_and_tele($receipt, $arrNoti);
                } else if ($warehouseType == SaleWarehouseChild::class) {
                    $receipt = SaleReceipt::query()->create(array_merge($baseData, [
                        'hub_issue_id' => $productIssue->id,
                        'warehouse_receipt_id' => $proposal->warehouse_id,
                        'warehouse_name' => $proposal->warehouse->name,
                        'warehouse_id' => $proposal->warehouse_issue_id,
                        'warehouse_type' => Warehouse::class,
                        'receipt_code' => $receiptCode,
                        'quantity' => $proposal->quantity,

                    ]));
                    // $arrNoti = [
                    //     'action' => 'tạo',
                    //     'permission' => "showroom-receipt.confirm",
                    //     'route' => route('showroom-receipt.confirmView', $receipt->id),
                    //     'status' => 'tạo'
                    // ];
                    // send_notify_cms_and_tele($receipt, $arrNoti);
                }
            }
        }

        return $receipt;
    }
    private function prepareReceiptDataFromProposal($productIssue, $proposal, $receiptCode)
    {
        return [
            'warehouse_receipt_id' => $proposal->warehouse_receipt_id ?: $proposal->warehouse_id,
            'proposal_id' => $proposal->id,
            'warehouse_name' => $proposal->warehouse_name,
            'warehouse_address' => $proposal->warehouse_address,
            'issuer_id' => $proposal->issuer_id,
            'invoice_issuer_name' => $proposal->invoice_issuer_name,
            'warehouse_id' => $productIssue->warehouse_issue_id,
            'warehouse_type' => Warehouse::class,
            'general_order_code' => $proposal->general_order_code,
            'quantity' => $proposal->quantity,
            'title' => $proposal->title,
            'description' => $proposal->description,
            'expected_date' => $proposal->expected_date,
            'receipt_code' => $receiptCode
        ];
    }
    private function prepareBaseReceiptData($productIssue, $proposal)
    {
        return [
            'proposal_id' => $proposal->id,
            'issuer_id' => $proposal->issuer_id,
            'invoice_issuer_name' => $proposal->invoice_issuer_name,
            'general_order_code' => $proposal->general_order_code,
            'description' => $productIssue->description,
            'expected_date' => $productIssue->expected_date,
            'title' => $proposal->title,
        ];
    }
    private function prepareSpecificReceiptData($proposal, $receiptCode)
    {
        return [
            'warehouse_receipt_id' => $proposal->warehouse_id,
            'warehouse_name' => $proposal->warehouse->name,
            'warehouse_address' => $proposal->warehouse->hub->address,
            'warehouse_id' => $proposal->warehouse_issue_id,
            'warehouse_type' => Warehouse::class,
            'quantity' => $proposal->quantity,
            'receipt_code' => $receiptCode
        ];
    }
    private function prepareSpecificReceiptDataForProduct($proposal, $newReceiptCode)
    {
        return [
            'isser_id' => $proposal->issuer_id,
            'warehouse_id' => $proposal->warehouse_issue_id,
            'warehouse_name' => $proposal->warehouse->name,
            'warehouse_address' => $proposal->warehouse->address,
            'wh_departure_id' => $proposal->warehouse_id,
            'wh_departure_name' => $proposal->warehouse_name,
            'is_warehouse' => 1,
            'quantity' => $proposal->quantity, // Make sure this is the correct field for quantity.
            'proposal_code' => $newReceiptCode,
        ];
    }
    private function createActualIssueDetail($actualIssue, $batch, $product)
    {
        if ($batch) {
            foreach ($batch->productInBatch as $batchDeatail) {
                $product = $batchDeatail->product;
                list($color, $size) = $this->extractColorAndSize($product->variationProductAttributes);
                $existingRecord = ActualIssueDetail::where([
                    'actual_id' => $actualIssue->id,
                    'product_id' => $product->id
                ])->first();
                if ($existingRecord) {
                    $existingRecord->update([
                        'quantity' => $existingRecord->quantity + 1,
                    ]);
                } else {
                    $actualIssueDetails = ActualIssueDetail::query()->create([
                        'actual_id' => $actualIssue->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => 1,
                        'is_batch' => 1,
                        'batch_id' => $batch->id,
                        'color' => $color,
                        'size' => $size,
                    ]);
                }
            }
        } else {
            list($color, $size) = $this->extractColorAndSize($product->variationProductAttributes);
            $existingRecord = ActualIssueDetail::where([
                'actual_id' => $actualIssue->id,
                'product_id' => $product->id
            ])->first();
            if ($existingRecord) {
                $existingRecord->update([
                    'quantity' => $existingRecord->quantity + 1,
                ]);
            } else {
                $actualIssueDetails = ActualIssueDetail::query()->create([
                    'actual_id' => $actualIssue->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => 1,
                    'is_batch' => 0,
                    'color' => $color,
                    'size' => $size,
                ]);
            }
        }
    }
    public function view($id)
    {
        $productIssue = HubIssue::where('id', $id)->first();
        $hubProposalIssuesId = $productIssue->proposal?->id;
        $productInput = IssueInputTour::query()
            ->select('id', 'proposal_issues_id', 'qrcode_id', 'product_id')
            ->where('proposal_issues_id', $hubProposalIssuesId)
            ->where('where_type', HubWarehouse::class)
            ->with(['product'])
            ->get();
        abort_if(check_user_depent_of_hub($productIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $actualIssue = ActualIssue::where('hub_issue_id', $productIssue->id)->with('autualDetail')->first();
        $batchs = $productIssue->actualQrCode;
        PageTitle::setTitle(trans('Chi tiết phiếu xuất HUB - :name', ['name' => BaseHelper::clean(get_proposal_issue_product_code($productIssue->issue_code))]));
        $a = (1 == 1);
        if ($a) {
            Assets::addScripts(['sortable'])
                ->addScriptsDirectly(
                    [
                        'vendor/core/plugins/gallery/js/gallery-admin.js',
                        'vendor/core/plugins/warehouse-finished-products/js/print-batch-qrcode.js',
                        "https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js",
                        'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js'
                    ]
                );

            return view('plugins/hub-warehouse::hub-issue.viewProduct', compact('productIssue', 'actualIssue', 'batchs', 'productInput'));
        } else {
            return view('plugins/hub-warehouse::hub-issue.view', compact('productIssue', 'actualIssue', 'productInput'));
        }
    }

    public function confirmReceiptInTour(Request $request, BaseHttpResponse $response)
    {
        $result = $this->hubIssueRepository->confirmReceiptInTour($request->all());

        if (!$result['error']) {
            return $response->setMessage($result['message']);
        } else {
            return $response->setError()->setMessage($result['message']);
        }
    }



    function applyWarehouseFilter($hubIssue, $response)
    {
        $hubUserIds = HubUser::where('user_id', \Auth::id())->pluck('hub_id')->toArray();

        if (!\Auth::user()->hasPermission('hub-warehouse.all-permissions')) {
            if (!in_array($hubIssue->warehouseIssue->hub_id, $hubUserIds)) {
                return false;
            }
        }

        return $hubIssue;
    }

    private function getProposal($productIssue)
    {
        if ($productIssue->from_proposal_receipt == 1) {
            if ($productIssue->warehouse_type == AgentWarehouse::class) {
                $proposal = ProposalAgentReceipt::find($productIssue->proposal->proposal_receipt_id);
                $proposal->update(['status' => ProposalAgentEnum::APPOROVED]);
            } else if ($productIssue->warehouse_type == ShowroomWarehouse::class) {
                $proposal = ShowroomProposalReceipt::find($productIssue->proposal->proposal_receipt_id);
                $proposal->update(['status' => ProposalAgentEnum::APPOROVED]);
            } else {
                $proposal = ProposalHubReceipt::where('id', $productIssue->proposal_id)->first();
                $proposal->update(['status' => ProposalReceiptProductEnum::CONFIRM]);
            }
        } else {
            $proposal = ProposalHubIssue::find($productIssue->proposal_id);
            $proposal->update(['status' => ProposalIssueStatusEnum::CONFIRM]);
        }


        return $proposal;
    }
    private function updateHubIssue($hubIssue)
    {
        $hubIssue->update([
            'status' => ProductIssueStatusEnum::APPOROVED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now()
        ]);
    }
    private function updateProductAndQrStatus($product, $qrCode, $qrCodeStatus)
    {
        $qrCode->status = $qrCodeStatus;
        $qrCode->save();
    }

    private function createQrHistory($action, $description, $createdBy, $qrCodeId)
    {
        ProductQrHistotry::create([
            'action' => $action,
            'description' => $description,
            'created_by' => $createdBy,
            'qrcode_id' => $qrCodeId
        ]);
    }
    private function checkAndUpdateStock($product, $quantityStockItem)
    {

        if ($quantityStockItem->quantity < 1) {
            throw new \Exception("Trong kho không còn sản phẩm $product->name");
        }
        $quantityStockItem->quantity--;
        $quantityStockItem->quantity_issue++;
        $quantityStockItem->save();
        return true;
    }

    public function processBatchIds($requestData, $response, $proposal)
    {
        DB::beginTransaction();
        try {
            if (isset($requestData['batch_ids'])) {
                foreach ($requestData['batch_ids'] as $batch_id) {
                    $batch = ProductBatch::find((int) $batch_id);
                    $batch->status = ProductBatchStatusEnum::OUTSTOCK;
                    $batch->save();

                    if ($batch->getQRCode) {
                        $qrCode = $batch->getQRCode;
                        $product = Product::find($batch->product_parent_id);
                        if (isset($proposal?->is_warehouse) && $proposal?->is_warehouse == 6) {
                            $this->updateProductAndQrStatus($product, $qrCode, QRStatusEnum::INTOUR);
                        } else {
                            $this->updateProductAndQrStatus($product, $qrCode, QRStatusEnum::PENDINGSTOCK);
                        }
                    }

                    // Xử lý chi tiết lô sản phẩm
                    $batchDetails = ProductBatchDetail::where('batch_id', (int) $batch_id)->get();
                    foreach ($batchDetails as $detail) {
                        $product = Product::find($detail->product_id);
                        if (!$product) {
                            throw new \Exception("Sản phẩm không tồn tại: " . $detail->product_id);
                        }

                        $quantityStockItem = QuantityProductInStock::where([
                            'stock_id' => $proposal->warehouse_issue_id ?: $proposal->warehouse_id,
                            'product_id' => $detail->product_id
                        ])->first();

                        if (!$quantityStockItem) {
                            throw new \Exception("Thông tin tồn kho không tồn tại cho sản phẩm: " . $product->name);
                        }
                        $check = $this->checkAndUpdateStock($product, $quantityStockItem);
                        if (isset($proposal?->is_warehouse) && $proposal?->is_warehouse == 6) {
                            $this->updateProductAndQrStatus($product, $detail->statusQrCode, QRStatusEnum::INTOUR);
                        } else {
                            $this->updateProductAndQrStatus($product, $detail->statusQrCode, QRStatusEnum::PENDINGSTOCK);
                        }
                    }
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    private function processQrIds($requestData, $proposal)
    {
        if (!isset($requestData['qr_ids'])) {
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($requestData['qr_ids'] as $qrId) {
                $qrCode = ProductQrcode::find($qrId);
                if (!$qrCode) {
                    throw new \Exception("Mã QR không tồn tại: $qrId");
                }

                // Cập nhật trạng thái sản phẩm và mã QR
                $product = Product::find($qrCode->reference_id);
                if (!$product) {
                    throw new \Exception("Sản phẩm không tồn tại cho mã QR: $qrId");
                }

                if (isset($proposal?->is_warehouse) && $proposal?->is_warehouse == 6) {
                    $this->updateProductAndQrStatus($product, $qrCode, QRStatusEnum::INTOUR);
                } else {
                    $this->updateProductAndQrStatus($product, $qrCode, QRStatusEnum::PENDINGSTOCK);
                }
                // $batchQuantity = $qrCode->batchParent->productBatch ?? null;
                // if (!$batchQuantity) {
                //     throw new \Exception("Lô sản phẩm không tồn tại cho mã QR");
                // }

                // $stock = $batchQuantity->warehouse->id ?? null;
                $productStock = QuantityProductInStock::where(['product_id' => $product->id, 'stock_id' => $qrCode->warehouse_id])->first();
                if (!$productStock) {
                    throw new \Exception("Thông tin tồn kho không tồn tại cho sản phẩm: " . $product->name);
                }
                $this->checkAndUpdateStock($product, $productStock);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    private function responseError($response, $message)
    {
        return $response->setError()->setMessage($message);
    }
    private function extractColorAndSize($arrAttribute)
    {
        $color = '';
        $size = '';

        foreach ($arrAttribute as $attribute) {
            if ($attribute->color) {
                $color = $attribute->title;
            } else {
                $size = $attribute->title;
            }
        }

        return [$color, $size];
    }

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        $hubIssue = HubIssue::where('id', $id)->sharedLock()->first();

        abort_if(check_user_depent_of_hub($hubIssue->warehouse_issue_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        DB::beginTransaction();
        try {
            if ($hubIssue->status != ProductIssueStatusEnum::PENDING) {
                return $response
                    ->setError()
                    ->setMessage('Phiếu đã bị hủy');
            }
            $proposal = $this->getProposal($hubIssue);
            $proposal->update(['status' => 'refuse', 'reason_cancel' => $request->input('denyReason')]);
            $hubIssue->proposal->update(['status' => 'refuse', 'reason_cancel' => $request->input('denyReason')]);
            $hubIssue->update(['status' => 'denied', 'reason_cancel' => $request->input('denyReason'), 'invoice_confirm_name' => Auth::user()->name]);

            $arrNoti = [
                'action' => 'từ chối',
                'permission' => "proposal-hub-issue.approve",
                'route' => route('hub-issue.view', $hubIssue->id),
                'status' => 'từ chối'
            ];
            send_notify_cms_and_tele($hubIssue, $arrNoti);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

        return $response
            ->setPreviousUrl(route('hub-issue.index'))
            ->setNextUrl(route('hub-issue.index'))
            ->setError()
            ->setMessage('Từ chối xuất kho');
    }

    public function getGenerateReceiptProduct(Request $request, HubIssueHelper $hubReceiptHelper)
    {
        $data = HubIssue::with('productIssueDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $hubReceiptHelper->streamInvoice($data);
        }
        return $hubReceiptHelper->downloadInvoice($data);
    }

    public function createBatchIssue(Request $request, $type)
    {
        $requestData = $request->input();

        DB::beginTransaction();

        //Lấy thông tin phiếu nhập kho
        try {
            $products = $requestData['products'] ?? [];
            $batchs = $requestData['batchs'] ?? [];

            $total = array_reduce($batchs, function ($carry, $batch) {
                return $carry + $batch['reference']['quantity'];
            }, 0);
            $total += count($products);
            if ($total == 0) {
                return response()->json(['err' => 1, 'msg' => 'Không có số lượng được quét'], 200);
            }
            $hubIssue = HubIssue::where('id', $requestData['id'])->first();
            $hubIssue->update([
                'status' => ProductIssueStatusEnum::PENDINGISSUE
            ]);
            $hubIssueDetail = $hubIssue->productIssueDetail;
            if ($type == 'batch') {
                $prefix = "BAT-HUB";
                $lastBatch = ProductBatch::orderByDesc('id')->first();
                if (empty($lastBatch)) {
                    $lastProductBatch = 1;
                    $batch_code = str_pad($lastProductBatch, 7, '0', STR_PAD_LEFT);
                } else {
                    $productBatch = (int) substr($lastBatch->batch_code, 7);
                    $batch_code = str_pad($productBatch + 1, 7, '0', STR_PAD_LEFT);
                }

                $batch_code_last = $prefix . $batch_code;
                $dataCreateBatch = [
                    'batch_code' => $batch_code_last,
                    'quantity' => $total,
                    'start_qty' => $total,
                    'status' => ProductBatchStatusEnum::OUTSTOCK,
                    'warehouse_id' => $hubIssue->warehouse_issue_id,
                    'warehouse_type' => Warehouse::class,
                    'product_parent_id' => 0,
                ];
                $productBatch = ProductBatch::query()->create($dataCreateBatch);

                $randomString = Str::random(7);
                $dateTimeNow = Carbon::now()->format('ymdHis');
                $qrCode = $randomString . $dateTimeNow;

                $qrCodeWithLogo = QrCode::size(150)->format('png')->merge('images/logo-handee.png', 0.3, true)->errorCorrection('H')->generate($qrCode);
                $proBatch = ProductQrcode::query()->create([
                    'qr_code' => $qrCode,
                    'base_code_64' => base64_encode($qrCodeWithLogo),
                    'batch_id' => $productBatch->id,
                    'status' => QRStatusEnum::PENDING,
                    'warehouse_id' => $hubIssue->warehouse_issue_id,
                    'warehouse_type' => Warehouse::class,
                    'reference_id' => $productBatch->id,
                    'created_by' => Auth::user()->id,
                    'reference_type' => ProductBatch::class,
                ]);
                ActualIssueQrCode::query()->create([
                    'issue_id' => $hubIssue->id,
                    'qrcode_id' => $proBatch->id,
                    'batch_id' => $productBatch->id,
                    'is_batch' => 1,
                ]);
                //Tạo thông tin chi tiết cho từng lô
                foreach ($batchs as $batch) {
                    $batchQrcode = ProductQrcode::find($batch['id']);
                    $batchQrcode->update([
                        'status' => QRStatusEnum::CANCELLED,
                    ]);
                    $batchParent = $batchQrcode->reference;
                    $batchParent->status = ProductBatchStatusEnum::ORTHER;
                    $batchParent->quantity = 0;
                    $batchParent->save();
                    foreach ($batchQrcode?->reference?->productInBatch as $detail) {
                        $detail->update([
                            'batch_id' => $productBatch->id,
                        ]);
                        $detail->statusQrCode->update([
                            'status' => QRStatusEnum::PENDING,
                        ]);
                        $this->historiesQRcode($detail->statusQrCode);
                        $this->incrementQrScan($hubIssueDetail, $detail['product_id']);
                        ActualIssueQrCode::query()->create([
                            'issue_id' => $hubIssue->id,
                            'qrcode_id' => $detail->statusQrCode->id,
                            'batch_id' => $productBatch->id,
                            'is_batch' => 0,
                            'product_id' => $detail['product_id']
                        ]);
                    }
                }
                foreach ($products as $product) {
                    ActualIssueQrCode::query()->create([
                        'issue_id' => $hubIssue->id,
                        'qrcode_id' => $product['id'],
                        'batch_id' => $productBatch->id,
                        'is_batch' => 0,
                        'product_id' => $product['reference_id']
                    ]);
                    $this->productSave($product, $hubIssueDetail);
                    $productQrCode = ProductQrcode::find($product['id']);
                    $batchDetail = $productQrCode?->batchParent;
                    if ($batchDetail) {
                        $batchDetail->update([
                            'batch_id' => $productBatch->id,
                        ]);
                    } else {
                        $prd = Product::find($product['reference_id']);
                        $dataCreateBatchDetail = [
                            'batch_id' => $productBatch->id,
                            'product_id' => $product['reference_id'],
                            'qrcode_id' => $product['id'],
                            'product_name' => $prd->name,
                            'sku' => $prd->sku,
                        ];
                        ProductBatchDetail::query()->create($dataCreateBatchDetail);
                    }
                }
                DB::commit();
                return response()->json(['view' => view('plugins/hub-warehouse::product-batch-qrcode.qrcode-batch', compact('proBatch'))->render(), 'batch' => $productBatch, 'batchDetail' => $productBatch->productInBatch]);
            } else {
                foreach ($batchs as $batch) {
                    $batchQrcode = ProductQrcode::find($batch['id']);
                    $batchQrcode->update([
                        'status' => QRStatusEnum::PENDING,
                    ]);

                    ActualIssueQrCode::query()->create([
                        'issue_id' => $hubIssue->id,
                        'qrcode_id' => $batchQrcode->id,
                        'batch_id' => $batch['reference_id'],
                        'is_batch' => 1,
                    ]);
                    foreach ($batchQrcode?->reference?->productInBatch as $detail) {
                        $detail?->statusQrCode->update([
                            'status' => QRStatusEnum::PENDING,
                        ]);
                        $this->historiesQRcode($detail?->statusQrCode);
                        $this->incrementQrScan($hubIssueDetail, $detail['product_id']);
                        ActualIssueQrCode::query()->create([
                            'issue_id' => $hubIssue->id,
                            'qrcode_id' => $detail->statusQrCode->id,
                            'batch_id' => $batch['reference_id'],
                            'is_batch' => 0,
                            'product_id' => $detail['product_id']
                        ]);
                    }
                }
                foreach ($products as $product) {
                    $this->productSave($product, $hubIssueDetail);
                    $productQrCode = ProductQrcode::find($product['id']);
                    $batchDetail = $productQrCode?->batchParent;
                    if ($batchDetail) {
                        $batchDetail->delete();
                    }
                    ActualIssueQrCode::query()->create([
                        'issue_id' => $hubIssue->id,
                        'qrcode_id' => $product['id'],
                        'is_batch' => 0,
                        'batch_id' => 0,
                        'product_id' => $product['reference_id']
                    ]);
                }
                DB::commit();
                return response()->json(['batch' => $batchs, 'product' => $products]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function historiesQRcode($productQrCode)
    {
        ProductQrHistotry::query()->create([
            'action' => 'receipt_stock',
            'created_by' => Auth::user()->id,
            'description' => 'Xác thực xuất kho hub thông qua việc quét mã QR của sản phẩm.',
            'qrcode_id' => $productQrCode->id,
        ]);
    }
    private function incrementQrScan($hubIssueDetail, $productId)
    {
        foreach ($hubIssueDetail as $detailIssue) {
            if ($detailIssue->product_id == $productId) {
                $detailIssue->quantity_scan += 1;
                $detailIssue->save();
            }
        }
    }
    private function productSave($product, $hubIssueDetail)
    {
        $productQrCode = ProductQrcode::find($product['id']);
        $productQrCode->update([
            'status' => QRStatusEnum::PENDING,
        ]);
        $batchDetail = $productQrCode?->batchParent?->productBatch;
        if ($batchDetail) {
            $batchDetail->quantity -= 1;
            $batchDetail->save();
        }
        $this->historiesQRcode($productQrCode);
        $this->incrementQrScan($hubIssueDetail, $product['reference_id']);
    }
}
