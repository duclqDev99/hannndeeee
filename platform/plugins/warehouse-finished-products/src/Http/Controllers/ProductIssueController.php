<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\HubReceiptDetail;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\ConfirmRequest;
use Botble\WarehouseFinishedProducts\Http\Requests\ProductIssueRequest;
use Botble\WarehouseFinishedProducts\Models\ActualIssue;
use Botble\WarehouseFinishedProducts\Models\ActualIssueDetail;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProductQrHistotry;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Botble\WarehouseFinishedProducts\Models\ReceiptProductDetail;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Botble\WarehouseFinishedProducts\Supports\ProductIssueHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Botble\WarehouseFinishedProducts\Tables\ProductIssueTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\WarehouseFinishedProducts\Forms\ProductIssueForm;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductIssueController extends BaseController
{

    // private $preXK = 'XK';
    // private $preNK = 'NK';
    public function index(ProductIssueTable $table)
    {
        PageTitle::setTitle('Danh sách phiếu thực xuất');
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/warehouse-finished-products::product-issue.create'));

        return $formBuilder->create(ProductIssueForm::class)->renderForm();
    }

    public function store(ProductIssueRequest $request, BaseHttpResponse $response)
    {
        $productIssue = ProductIssue::query()->create($request->input());

        event(new CreatedContentEvent(GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $productIssue));

        return $response
            ->setPreviousUrl(route('product-issue.index'))
            ->setNextUrl(route('product-issue.edit', $productIssue->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ProductIssue $productIssue, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $productIssue->name]));

        return $formBuilder->create(ProductIssueForm::class, ['model' => $productIssue])->renderForm();
    }

    public function update(ProductIssue $productIssue, ProductIssueRequest $request, BaseHttpResponse $response)
    {
        $productIssue->fill($request->input());

        $productIssue->save();

        event(new UpdatedContentEvent(GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $productIssue));

        return $response
            ->setPreviousUrl(route('product-issue.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ProductIssue $productIssue, Request $request, BaseHttpResponse $response)
    {
        try {
            $productIssue->delete();

            event(new DeletedContentEvent(GOOD_RECEIPTS_MODULE_SCREEN_NAME, $request, $productIssue));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function viewConfirm(int|string $id, BaseHttpResponse $response)
    {

        Assets::addScriptsDirectly([
            'vendor/core/plugins/gallery/js/gallery-admin.js',
            'vendor/core/plugins/warehouse-finished-products/js/product-issue.js',
        ])->addStylesDirectly([
                    'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css',
                    'vendor/core/plugins/warehouse-finished-products/css/product-issue-form.css',
                ]);
        PageTitle::setTitle('Phiếu thực xuất kho');
        $warehouseId = ProductIssue::where('id', $id)->pluck('warehouse_id')->first();
        $productIssue = ProductIssue::where('id', $id)->first();
        $productIssue = $this->applyWarehouseFilter($productIssue);
        if ($productIssue->status->toValue() !== ProductIssueStatusEnum::PENDING) {
            return $response
            ->setError()
            ->setMessage(__('Đơn hàng đã duyệt hoặc từ chối!'));
        }
        return view('plugins/warehouse-finished-products::product-issue.confirm', compact('productIssue'));

    }

    public function confirmProductIssue(int|string $id, ConfirmRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        if (empty($requestData['batch_ids']) && empty($requestData['qr_ids'])) {
            return $this->responseError($response, 'Không tồn tại mã QR để sử dụng');
        }
        $productIssue = ProductIssue::find($id);
        if (!$productIssue) {
            return $this->responseError($response, 'Không tồn tại phiếu xuất');
        }
        $proposal = $this->getProposal($productIssue);
        DB::beginTransaction();
        try {
            $this->updateProductIssue($productIssue, $requestData['images']);
            $proposal->update(['status' => ProposalProductEnum::CONFIRM]);
            $totalQuantity = 0;
            $processBatch = $this->processBatchIds($requestData, $productIssue);
            if ($processBatch !== true) {
                DB::rollBack();

                return $this->responseError($response, $processBatch);
            }
            $processQR = $this->processQrIds($requestData, $proposal, $productIssue);
            if ($processQR !== true) {
                DB::rollBack();
                return $this->responseError($response, $processQR);
            }
            $filteredImages = array_filter($requestData['images']);
            $imageJson = json_encode($filteredImages);
            $dataActual = [
                'product_issue_id' => $productIssue->id,
                'general_order_code' => $productIssue->general_order_code,
                'warehouse_issue_id' => $productIssue->warehouse_id,
                'warehouse_name' => $productIssue->warehouse_name,
                'warehouse_address' => $productIssue->warehouse_address,
                'invoice_confirm_name' => $productIssue->invoice_confirm_name,
                'warehouse_id' => $productIssue->warehouse_receipt_id,
                'warehouse_type' => $productIssue->warehouse_type,
                'is_warehouse' => $productIssue->is_warehouse,
                'image' => $imageJson
            ];
            $actual = ActualIssue::query()->create($dataActual);
            $lastReceiptProduct = ReceiptProduct::orderByDesc('id')->first();
            $receiptCode = $lastReceiptProduct ? (int) $lastReceiptProduct->receipt_code + 1 : 1;

            if ($productIssue->from_proposal_receipt == 1) {

                if ($productIssue->warehouse_type == WarehouseFinishedProducts::class) {
                    $dataInsert = [
                        'general_order_code' => $proposal->general_order_code,
                        'proposal_id' => $proposal->id,
                        'warehouse_id' => $proposal->warehouse_id,
                        'warehouse_name' => $proposal->warehouse_name,
                        'warehouse_address' => $proposal->warehouse_address,
                        'isser_id' => $proposal->isser_id,
                        'invoice_issuer_name' => $proposal->invoice_issuer_name,
                        'wh_departure_id' => $proposal->wh_departure_id,
                        'wh_departure_name' => $proposal->wh_departure_name,
                        'is_warehouse' => $proposal->is_warehouse,
                        'quantity' => $proposal->quantity,
                        'title' => 'Nhập thành phẩm tử ' . $proposal->wh_departure_name,
                        'description' => $productIssue->description,
                        'expected_date' => $productIssue->expected_date,
                        'from_product_issue' => 1,
                        'receipt_code' => $receiptCode,
                    ];
                    $productReceipt = ReceiptProduct::query()->create($dataInsert);

                    $proposal->update(['proposal_code' => $receiptCode]);
                } else {
                    $lastHubReceipt = HubReceipt::orderByDesc('id')->first();
                    $hubReceiptCode = $lastHubReceipt ? (int) $lastHubReceipt->receipt_code + 1 : 1;
                    $data = [
                        'warehouse_receipt_id' => $proposal->warehouse_receipt_id,
                        'proposal_id' => $proposal->id,
                        'warehouse_name' => $proposal->warehouse_name,
                        'warehouse_address' => $proposal->warehouse_address,
                        'issuer_id' => $proposal->issuer_id,
                        'invoice_issuer_name' => $proposal->invoice_issuer_name,
                        'warehouse_id' => $proposal->warehouse_id,
                        'warehouse_type' => $proposal->warehouse_type,
                        'general_order_code' => $proposal->general_order_code,
                        'quantity' => $proposal->quantity,
                        'title' => $proposal->title,
                        'description' => $proposal->quantity,
                        'expected_date' => $proposal->expected_date,
                        'receipt_code' => $hubReceiptCode,
                        'issue_id' => $productIssue->id
                    ];
                    $hubReceipt = HubReceipt::query()->create($data);
                    $proposal->update(['proposal_code' => $hubReceiptCode]);
                }

            } else {

                // Define the base data array
                $baseData = [
                    'proposal_id' => $proposal->id,
                    'issuer_id' => $proposal->issuer_id,
                    'invoice_issuer_name' => $proposal->invoice_issuer_name,
                    'general_order_code' => $proposal->general_order_code,
                    'description' => $productIssue->description,
                    'expected_date' => $productIssue->expected_date,
                    'title' => $proposal->title,
                ];

                if ($proposal->is_warehouse == 1) {
                    $specificData = [
                        'isser_id' => $proposal->issuer_id,
                        'warehouse_id' => $proposal->warehouse_receipt_id,
                        'warehouse_name' => $proposal->warehouse->name,
                        'warehouse_address' => $proposal->warehouse->address,
                        'wh_departure_id' => $proposal->warehouse_id,
                        'wh_departure_name' => $proposal->warehouse_name,
                        'is_warehouse' => 1,
                        'quantity' => $proposal->quantity,
                        'receipt_code' => $receiptCode,
                    ];

                    $productReceipt = ReceiptProduct::query()->create(array_merge($baseData, $specificData));
                } else {
                    $lastHubReceipt = HubReceipt::orderByDesc('id')->first();
                    // $lastNumber = $lastHubReceipt ? intval(substr($lastHubReceipt->receipt_code, 2)) : 0;
                    // $nextNumber = str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                    // $hubReceiptCode = $this->preNK . $nextNumber;
                    $hubReceiptCode = $lastHubReceipt ? (int) $lastHubReceipt->receipt_code + 1 : 1;


                    $specificData = [
                        'warehouse_receipt_id' => $proposal->warehouse_receipt_id,
                        'warehouse_name' => $proposal->warehouse->name,
                        'warehouse_address' => $proposal->warehouse->hub->address,
                        'warehouse_id' => $proposal->warehouse_id,
                        'warehouse_type' => WarehouseFinishedProducts::class,
                        'quantity' => $proposal->quantity,
                        'receipt_code' => $hubReceiptCode,
                        'issue_id' => $productIssue->id

                    ];
                    $hubReceipt = HubReceipt::query()->create(array_merge($baseData, $specificData));
                }
            }
            if (isset($requestData['batch_ids'])) {
                foreach ($requestData['batch_ids'] as $key => $batch_id) {
                    // Retrieve the ProductBatch based on the batch ID

                    $batch = ProductBatch::find((int) $batch_id);
                    $batch->status = ProductBatchStatusEnum::OUTSTOCK;
                    if (isset($productReceipt)) {
                        $batch->receipt_id = $productReceipt->id;
                    } else if (isset($hubReceipt)) {
                        $batch->receipt_id = $hubReceipt->id;
                    }
                    $batch->save();
                    if ($batch) {
                        foreach ($batch->productInBatch as $detailBatch) {
                            $product = Product::find((int) $detailBatch->product_id);
                            // Check if the record already exists
                            $existingDetail = ActualIssueDetail::where('actual_id', $actual->id)
                                ->where('product_id', $product->id)
                                ->first();

                            if ($existingDetail) {
                                // If the record exists, increment the quantity
                                $existingDetail->quantity += 1;
                                $existingDetail->save();
                            } else {
                                list($color, $size) = $this->extractColorAndSize($product->variationProductAttributes);
                                // If the record doesn't exist, create a new record
                                $dataActualDetail = [
                                    'actual_id' => $actual->id,
                                    'product_id' => $product->id,
                                    'product_name' => $product->name,
                                    'sku' => $product->sku,
                                    'price' => $product->price,
                                    'quantity' => 1,
                                    'qrcode_id' => $detailBatch->qrcode_id,
                                    'is_batch' => 0,
                                ];
                                ActualIssueDetail::create($dataActualDetail);
                            }
                        }
                        $prdParent = Product::find($batch->product_parent_id);
                        $data = [
                            'batch_id' => $batch_id,
                            'product_id' => $batch->product_parent_id,
                            'product_name' => $prdParent->name,
                            'price' => $prdParent->price,
                            'sku' => $prdParent->sku,
                            'quantity' => 1,
                        ];
                        if (isset($productReceipt)) {
                            ReceiptProductDetail::query()->create(array_merge($data, ['receipt_id' => $productReceipt->id,]));
                        } else {
                            HubReceiptDetail::query()->create(array_merge($data, ['hub_receipt_id' => $hubReceipt->id]));
                        }

                    }
                }
            }
            if (isset($requestData['qr_ids'])) {
                foreach ($requestData['qr_ids'] as $key => $qrId) {
                    $qrCode = ProductQrcode::find($qrId);

                    $product = Product::find((int) $qrCode->productBatchDetail->product_id);

                    $dataActualDetail = [
                        'actual_id' => $actual->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => 1,
                        'qrcode_id' => $qrId,
                        'is_batch' => 0,
                    ];
                    ActualIssueDetail::create($dataActualDetail);
                    if ($qrCode) {
                        $arrAttribute = $product->variationProductAttributes;
                        list($color, $size) = $this->extractColorAndSize($arrAttribute);
                        $productId = $product->id;
                        $data = [
                            'batch_id' => null,
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'price' => $product->price,
                            'sku' => $product->sku,
                            'quantity' => 1,
                            'color' => $color,
                            'size' => $size,
                            'qrcode_id' => $qrId,
                            'is_odd' => 1
                        ];

                        if (isset($productReceipt)) {
                            $receiptDetail = ReceiptProductDetail::create(array_merge($data, ['receipt_id' => $productReceipt->id]));
                        } else {
                            HubReceiptDetail::create(array_merge($data, ['hub_receipt_id' => $hubReceipt->id]));
                        }
                        $qrCode->productBatchDetail->delete();

                    }
                }


            }

            $arrNoti = [
                'action' => 'xác nhận',
                'permission' => "proposal-product-issue.approve",
                'route' => route('product-issue.view', $productIssue->id),
                'status' => 'xác nhận'
            ];
            send_notify_cms_and_tele($productIssue, $arrNoti);
            $arrNoti = [
                'action' => 'xác nhận',
                'permission' => "proposal-product-issue.approve",
                'route' => route('product-issue.view', $productIssue->id),
                'status' => 'xác nhận'
            ];
            send_notify_cms_and_tele($productIssue, $arrNoti);
            DB::commit();
            return $this->responseSuccess($response, trans('Đã xuất kho'));

        } catch (Exception $exception) {
            DB::rollBack();
            return $this->responseError($response, $exception->getMessage());
        }

    }

    public function exportProductIssueDetail($id, ProductIssueHelper $materialHelper, Request $request)
    {
        $data = ProductIssue::with('productIssueDetail')->find($id);
        $requestData = $request->input();
        if ($requestData['button_type'] === 'print') {
            return $materialHelper->streamInvoice($data);
        }
        return $materialHelper->downloadInvoice($data);
    }
    public function viewProductIssueDetail($id)
    {
        $productIssue = ProductIssue::where('id', $id)
            ->with('productIssueDetail')
            ->first();
        PageTitle::setTitle(__('Thông tin xuất kho thành phẩm - phiếu :name', ['name' => BaseHelper::clean(get_proposal_issue_product_code($productIssue->issue_code))]));

        $productIssue = $this->applyWarehouseFilter($productIssue);
        $actualIssue = ActualIssue::where('product_issue_id', $productIssue->id)->first();
        $batchs = $productIssue->productBatch;
        return view('plugins/warehouse-finished-products::product-issue.view', compact('productIssue', 'actualIssue'));
    }
    public function getMoreQuantity(Request $request)
    {
        $requestData = $request->input();
        $quantity = min($requestData['quantity'], $requestData['quantityStock']);
        $productBatch = ProductBatch::where('product_id', $requestData['product_id'])->where('stock_id', $requestData['warehouse_id'])->where('quantity', '>', 0)
            ->get();
        $selectedBatches = [];
        $remainingQuantity = $quantity;
        $product = Product::find($requestData['product_id']);

        foreach ($productBatch as $batch) {
            if ($batch->quantity >= $remainingQuantity) {
                $selectedBatches[] = [
                    'batch_id' => $batch->id,
                    'batch_quantity' => $batch->quantity,
                    'quantity' => $remainingQuantity,
                    'batch_code' => $batch->batch_code,
                    'quantityStock' => $requestData['quantityStock'],
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'img' => $product->image,
                ];
                break;
            } else {
                $selectedBatches[] = [
                    'batch_id' => $batch->id,
                    'batch_quantity' => $batch->quantity,
                    'quantity' => $batch->quantity,
                    'batch_code' => $batch->batch_code,
                    'quantityStock' => $requestData['quantityStock'],
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'img' => $product->image,
                ];

                $remainingQuantity -= $batch->quantity;
            }
        }
        return ($selectedBatches);
    }

    public function deniedProductIssue(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $productIssue = ProductIssue::findOrFail($id);
        $proposal = $this->getProposal($productIssue);
        $proposal->update(['status' => ProposalProductEnum::REFUSE]);
        $productIssue->update(['status' => 'denied', 'reason' => $request->input('denyReason'), 'invoice_confirm_name' => Auth::user()->name]);


        $arrNoti = [
            'action' => 'từ chối xuất',
            'permission' => "proposal-product-issue.approve",
            'route' => route('product-issue.view', $productIssue->id),
            'status' => 'từ chối xuất'
        ];
        send_notify_cms_and_tele($productIssue, $arrNoti);
        return $response
            ->setPreviousUrl(route('product-issue.index'))
            ->setPreviousUrl(route('product-issue.index'))
            ->setMessage('Hủy xuất kho thành phẩm thành công');
    }
    function applyWarehouseFilter($productIssue)
    {
        if (!request()->user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            if (!in_array($productIssue->warehouse_id, $warehouseIds)) {
                abort(403, 'Không có quyền xem');
            }
        }
        return $productIssue;
    }
    private function extractColorAndSize($arrAttribute)
    {
        $color = '';
        $size = '';

        if (count($arrAttribute) > 0) {
            $color = $arrAttribute[0]->color == null ? '' : $arrAttribute[0]->title;
            $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : '';

            if (count($arrAttribute) === 2) {
                $color = $arrAttribute[0]->color == null ? $arrAttribute[1]->title : $arrAttribute[0]->title;
                $size = $arrAttribute[0]->color == null ? $arrAttribute[0]->title : $arrAttribute[1]->title;
            }
        }

        return [$color, $size];
    }
    private function responseError($response, $message)
    {
        return $response->setError()->setMessage($message);
    }
    private function responseSuccess($response, $message)
    {
        return $response
            ->setPreviousUrl(route('product-issue.index'))
            ->setNextUrl(route('product-issue.index'))
            ->setMessage($message);
    }
    private function getProposal($productIssue)
    {
        if ($productIssue->from_proposal_receipt == 1) {
            if ($productIssue->warehouse_type == WarehouseFinishedProducts::class) {
                $proposal = ProposalReceiptProducts::where('id', $productIssue->proposal_id)->first();
            } else {
                $proposal = ProposalHubReceipt::where('id', $productIssue->proposal_id)->first();
            }
        } else {
            $proposal = ProposalProductIssue::find($productIssue->proposal_id);
        }
        return $proposal;
    }
    private function updateProductIssue($productIssue, $image)
    {
        $productIssue->update([
            'status' => ProductIssueStatusEnum::APPOROVED,
            'invoice_confirm_name' => Auth::user()->name,
            'date_confirm' => Carbon::now(),
        ]);
    }
    private function processBatchIds($requestData, $productIssue)
    {
        if (!isset($requestData['batch_ids'])) {
            return true;
        }
        try {
            foreach ($requestData['batch_ids'] as $batch_id) {
                $this->processSingleBatchId((int) $batch_id, $productIssue);

            }
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
    private function processSingleBatchId($batch_id, $productIssue)
    {
        $batch = ProductBatch::find($batch_id);
        if (!$batch || !$batch->batchQrCode) {
            throw new \Exception("Lô hoặc QR Code không tìm thấy trong lô {$batch->name}.");
        }
        $this->updateStatusAndSave($batch->batchQrCode, QRStatusEnum::PENDINGSTOCK);
        $batchDetails = ProductBatchDetail::where('batch_id', $batch_id)->get();
        foreach ($batchDetails as $detail) {
            $this->processBatchDetail($detail, $productIssue);
        }
    }
    private function processBatchDetail($detail, $productIssue)
    {
        if ($detail->statusQrCode->status != QRStatusEnum::INSTOCK) {
            throw new \Exception("Sản phẩm không nằm trong kho");
        }
        $this->updateProductAndStock($detail->product_id, $productIssue->warehouse_id);
        $this->updateStatusAndSave($detail->statusQrCode, QRStatusEnum::PENDINGSTOCK);
        $this->logProductQrHistory($detail->statusQrCode->id, 'out_stock', 'Xác thực xuất kho thành phẩm thông qua việc quét mã QR của sản phẩm.');
    }
    private function processQrIds($requestData, $proposal, $productIssue)
    {
        if (!isset($requestData['qr_ids'])) {
            return true;
        }

        $stockId = $productIssue->from_proposal_receipt == 1 ? ($proposal->wh_departure_id ?: $proposal->warehouse_id) : $proposal->warehouse_id;
        try {
            foreach ($requestData['qr_ids'] as $qrId) {
                $this->processSingleQrId($qrId, $proposal, $stockId);
            }
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
    private function processSingleQrId($qrId, $proposal, $stockId)
    {
        $qrCode = ProductQrcode::find($qrId);

        if (!$qrCode || !isset($qrCode->productBatchDetail)) {
            throw new \Exception("Không tìm thấy mã QR hoặc mã QR không nằm trong kho này. $qrCode->id");
        }
        $status = QRStatusEnum::PENDINGSTOCK;
        $this->updateProductAndStock($qrCode->reference_id, $stockId);
        $this->updateStatusAndSave($qrCode, $status);
        $this->updateProductBatchDetail($qrCode->productBatchDetail, $stockId);
        $this->logProductQrHistory($qrId, 'out_stock', 'Xác thực xuất kho thành phẩm thông qua việc quét mã QR của sản phẩm.');
    }
    private function updateStatusAndSave($entity, $status)
    {
        $entity->status = $status;
        $entity->save();
    }

    private function updateProductAndStock($productId, $stockId)
    {
        $product = Product::find($productId);
        $quantityStockItem = QuantityProductInStock::where([
            'stock_id' => $stockId,
            'product_id' => $productId
        ])->first();
        if ($quantityStockItem->quantity <= 0 || $product->quantity <= 0) {
            throw new \Exception("Trong kho không còn sản phẩm {$product->name}.");
        }
        $quantityStockItem->decrement('quantity');
        $quantityStockItem->increment('quantity_issue');
        // $product->decrement('quantity');
    }

    private function updateProductBatchDetail($productBatchDetail, $stockId)
    {
        if ($productBatchDetail->productBatch->quantity <= 0) {
            throw new \Exception("Trong lô không còn sản phẩm {$productBatchDetail->productBatch->product->name}");
        }
        $productBatchDetail->productBatch->decrement('quantity');
        $productBatchDetail->productBatch->save();
    }

    private function logProductQrHistory($qrCodeId, $action, $description)
    {
        ProductQrHistotry::create([
            'action' => $action,
            'description' => $description,
            'created_by' => Auth::user()->id,
            'qrcode_id' => $qrCodeId
        ]);
    }
}
