<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Auth;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Facades\Assets;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Http\Requests\HubReceiptRequest;
use Botble\HubWarehouse\Models\ActualReceiptDetail;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductIssueStatusEnum;
use Botble\HubWarehouse\Models\ActualReceipt;
use Botble\WarehouseFinishedProducts\Enums\ProposalReceiptProductEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\Widget\Events\RenderingWidgetSettings;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Exception;
use Botble\HubWarehouse\Tables\HubReceiptTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\HubWarehouse\Forms\HubReceiptForm;
use Botble\Base\Forms\FormBuilder;
use Botble\HubWarehouse\Models\ActualReceiptQrcode;
use Botble\HubWarehouse\Models\HubActualReceiptBatch;
use Botble\HubWarehouse\Supports\HubReceiptHelper;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\ExportBillRequest;

class HubReceiptController extends BaseController
{
    public function index(HubReceiptTable $table)
    {
        PageTitle::setTitle(trans('Danh sách phiếu'));

        return $table->renderTable();
    }

    public function confirm($id, BaseHttpResponse $response)
    {

        Assets::addScripts(['sortable'])
            ->addScriptsDirectly(
                [
                    'vendor/core/plugins/gallery/js/gallery-admin.js',
                    'vendor/core/plugins/hub-warehouse/js/batch.js'
                ]
            )->addStylesDirectly('vendor/core/packages/widget/css/widget.css');

        RenderingWidgetSettings::dispatch();
        $this->pageTitle('Phiếu thực nhập kho');

        $receipt = HubReceipt::where('id', $id)->first();
        abort_if(check_user_depent_of_hub($receipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if (!$receipt) {
            return $response
                ->setError()
                ->setMessage(__('Không tồn tại đơn!'));
        }

        if ($receipt->status->toValue() !== ProductIssueStatusEnum::PENDING) {
            return $response
                ->setError()
                ->setMessage(__('Đơn đã nhập kho!'));
        }
        return view('plugins/hub-warehouse::hub-receipt.confirm', compact('receipt'));
    }

    public function confirmReceipt(Request $request, HubReceipt $receipt, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        $receipt = HubReceipt::where('id', $receipt->id)->sharedLock()->first();

        abort_if(check_user_depent_of_hub($receipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($receipt->status == ApprovedStatusEnum::PENDING) {
            try {
                $requestData = $request->input();
                if ($receipt->receiptDetail[0]->batch_id) {
                    $productBatch = [];
                    foreach ($receipt->receiptDetail as $receiptDt) {
                        $productBatch[] = ProductBatch::find($receiptDt->batch_id);
                    }
                } else {
                    $productBatch = ProductBatch::where(['receipt_id' => $receipt->id, 'warehouse_type' => Warehouse::class, 'warehouse_id' => $receipt->warehouse_receipt_id])->get();
                }
                $listProductScan = ActualReceiptQrcode::query()->where(['receipt_id' => $receipt->id])->get();
                $totalQuantity = $listProductScan->count();
                $receipt->update([
                    'status' => ProductIssueStatusEnum::APPOROVED,
                    'invoice_confirm_name' => Auth::user()->name,
                    'date_confirm' => Carbon::now()->format('Y-m-d'),
                ]);
                if ($receipt->warehouse_type !== AgentWarehouse::class && $receipt->warehouse_type != ShowroomWarehouse::class) {
                    if ($receipt->issue_id) {
                        $receipt->issue->proposalHubReceipt->update([
                            'status' => ProposalProductEnum::APPOROVED,
                            'invoice_confirm_name' => Auth::user()->name,
                            'date_confirm' => Carbon::now()->format('Y-m-d'),
                        ]);
                    } else {
                        if (!empty($receipt->proposal)) {
                            $receipt->proposal->update([
                                'status' => ProposalProductEnum::APPOROVED,
                                'invoice_confirm_name' => Auth::user()->name,
                                'date_confirm' => Carbon::now()->format('Y-m-d'),
                            ]);
                        }
                    }
                }

                $dataInsertActual = [
                    'receipt_id' => $receipt->id,
                    'general_order_code' => $receipt->general_order_code,
                    'warehouse_receipt_id' => $receipt->warehouse_receipt_id,
                    'warehouse_name' => $receipt->warehouse_name,
                    'warehouse_address' => $receipt->warehouse_address,
                    'invoice_confirm_name' => Auth::user()->name,
                    'quantity' => $totalQuantity,
                    'warehouse_id' => $receipt->warehouse_id,
                    'warehouse_type' => $receipt->warehouse_type,
                    'status' => ProductIssueStatusEnum::APPOROVED,
                    'image' => $requestData['gallery']
                ];
                $actual = ActualReceipt::query()->create($dataInsertActual);
                // Create event for admin
                $arrNoti = [
                    'action' => 'xác nhận',
                    'permission' => "hub-receipt.create",
                    'route' => route('hub-receipt.view', $receipt->id),
                    'status' => 'đã nhập kho'
                ];
                send_notify_cms_and_tele($actual, $arrNoti);

                $arrProductActual = [];

                foreach ($listProductScan as $key => $item) {
                    $product = $item->product;

                    $stockBy = QuantityProductInStock::where(['stock_id' => $receipt->warehouse_receipt_id, 'product_id' => $item->product_id])->first();
                    if (!empty($stockBy)) {
                        $qty = (int) $stockBy->quantity + 1;
                        $stockBy->update(['quantity' => $qty]);
                    } else {
                        $dataInsert = [
                            'stock_id' => $receipt->warehouse_receipt_id,
                            'product_id' => $item->product_id,
                            'quantity' => 1,
                        ];
                        QuantityProductInStock::create($dataInsert);
                    }

                    //Cập nhật số lượng cho sản phảm thương mại điện tử
                    // $product->update([
                    //     'quantity' => (int) $product->quantity + 1,
                    // ]);
                    // $productParent = $product->parentProduct->first();
                    // if ($productParent) {
                    //     $productParent->update([
                    //         'quantity' => (int) $productParent->quantity + 1,
                    //     ]);
                    // }
                    new UpdatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, $request, $product);

                    //Thêm dữ liệu vào mảng chi tiết thực nhập sản phẩm
                    if (array_key_exists($item->product_id, $arrProductActual)) {
                        $arrProductActual[$item->product_id] += 1;
                    } else {
                        $arrProductActual[$item->product_id] = 1;
                    }
                }

                $productBatch = ProductBatch::where(['receipt_id' => $receipt->id, 'warehouse_type' => Warehouse::class])->get();
                foreach ($productBatch as $key => $values) {
                    //Lưu lại lịch sử các lô đã tạo của đơn
                    HubActualReceiptBatch::create([
                        'actual_id' => $actual->id,
                        'batch_id' => $values->id,
                        'quantity' => $values->quantity,
                        'start_qty' => $values->start_qty,
                    ]);
                }

                foreach ($arrProductActual as $proId => $quantity) {
                    $product = Product::where('id', $proId)->first();
                    $dataInsertActualDetail = [
                        'actual_id' => $actual->id,
                        'product_id' => $proId,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => (int) $quantity,
                        'price' => $product->price,
                        'reasoon' => null
                    ];
                    ActualReceiptDetail::create($dataInsertActualDetail);
                }
            } catch (Exception $err) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($err->getMessage());
            }
            //DB commit
            DB::commit();
            return $response
                ->setPreviousUrl(route('hub-receipt.index'))
                ->setNextUrl(route('hub-receipt.view', $receipt))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } else {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage('Đơn đã được xác nhận');
        }
    }
    public function confirmProduct(Request $request, HubReceipt $receipt, BaseHttpResponse $response)
    {
        $requestData = $request->input();

        abort_if(check_user_depent_of_hub($receipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        if ($receipt->status == ProductIssueStatusEnum::PENDING) {
            $totalQuantity = 0;
            foreach ($requestData['qr_ids'] as $qrId) {
                $totalQuantity++;
            }
            $filteredImages = array_filter($requestData['images']);
            $imageJson = json_encode($filteredImages);
            DB::beginTransaction();
            try {
                $receipt->update([
                    'status' => ProductIssueStatusEnum::APPOROVED,
                    'invoice_confirm_name' => Auth::user()->name,
                    'date_confirm' => Carbon::now()->format('Y-m-d'),
                ]);
                $dataInsertActual = [
                    'receipt_id' => $receipt->id,
                    'general_order_code' => $receipt->general_order_code,
                    'warehouse_receipt_id' => $receipt->warehouse_receipt_id,
                    'warehouse_name' => $receipt->warehouse_name,
                    'warehouse_address' => $receipt->warehouse_address,
                    'invoice_confirm_name' => Auth::user()->name,
                    'quantity' => $totalQuantity,
                    'warehouse_id' => $receipt->warehouse_id,
                    'warehouse_type' => $receipt->warehouse_type,
                    'status' => ProductIssueStatusEnum::APPOROVED,
                    'image' => $imageJson
                ];
                $actual = ActualReceipt::query()->create($dataInsertActual);
                foreach ($requestData['qr_ids'] as $qrId) {
                    $qrCode = ProductQrcode::find($qrId);
                    $qrCode->update([
                        'warehouse_type' => Warehouse::class,
                        'warehouse_id' => $receipt->warehouse_receipt_id,
                        'status' => QRStatusEnum::INSTOCK,
                    ]);
                    $quantityStock = QuantityProductInStock::where(['stock_id' => $receipt->warehouse_id, 'product_id' => $qrCode->reference_id])->first();
                    if ($quantityStock) {
                        $quantityStock->update(['quantity' => DB::raw('quantity + 1')]);
                    } else {
                        QuantityProductInStock::query()->create([
                            'stock_id' => $receipt->warehouse_id,
                            'product_id' => $qrCode->reference_id,
                            'quantity' => 1
                        ]);
                    }
                    $product = Product::find($qrCode->reference_id);
                    $actualDetai = ActualReceiptDetail::where(['product_id' => $qrCode->reference_id, 'actual_id' => $actual->id])->first();
                    if ($actualDetai) {
                        $actualDetai->update(['quantity' => DB::raw('quantity + 1')]);
                    } else {
                        $dataInsertActualDetail = [
                            'actual_id' => $actual->id,
                            'product_id' => $qrCode->reference_id,
                            'product_name' => $product->name,
                            'sku' => $product->sku,
                            'quantity' => (int) 1,
                            'price' => $product->price,
                            'reasoon' => null,
                        ];
                        ActualReceiptDetail::create($dataInsertActualDetail);
                    }
                }
                $arrNoti = [
                    'action' => 'xác nhận',
                    'permission' => "hub-receipt.create",
                    'route' => route('hub-receipt.view', $receipt->id),
                    'status' => 'đã nhập kho'
                ];
                send_notify_cms_and_tele($actual, $arrNoti);
                DB::commit();
                return $response
                    ->setPreviousUrl(route('hub-receipt.index'))
                    ->setNextUrl(route('hub-receipt.index'))
                    ->setMessage(trans('core/base::notices.create_success_message'));

            } catch (Exception $e) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($e->getMessage());
            }

        } else {
            return $response
                ->setError()
                ->setMessage('Đơn đã được xác nhận');
        }
    }
    public function view($id)
    {
        PageTitle::setTitle('Thông tin nhập kho hub');

        $receipt = HubReceipt::find($id);

        abort_if(check_user_depent_of_hub($receipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');
        // $receipt = $this->applyWarehouseFilter($receipt);
        $actual = ActualReceipt::where(['receipt_id' => $receipt->id])->with('actualDetail')->first();


        $a = (1 == 2);
        if ($a) {
            return view('plugins/hub-warehouse::hub-receipt/viewProduct', compact('receipt', 'actual'));
        } else {

            Assets::addScripts(['sortable'])
                ->addScriptsDirectly(
                    [
                        'vendor/core/plugins/gallery/js/gallery-admin.js',
                        'vendor/core/plugins/warehouse-finished-products/js/print-batch-qrcode.js',
                        "https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js",
                        'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js'
                    ]
                );

            $batchs = $actual?->batch;
            return view('plugins/hub-warehouse::hub-receipt/view', compact('receipt', 'actual', 'batchs'));
        }
    }
    public function edit(HubReceipt $hubReceipt, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $hubReceipt->name]));

        abort_if(check_user_depent_of_hub($hubReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        return $formBuilder->create(HubReceiptForm::class, ['model' => $hubReceipt])->renderForm();
    }

    public function update(HubReceipt $hubReceipt, HubReceiptRequest $request, BaseHttpResponse $response)
    {
        abort_if(check_user_depent_of_hub($hubReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        $hubReceipt->fill($request->input());

        $hubReceipt->save();

        event(new UpdatedContentEvent(HUB_RECEIPT_MODULE_SCREEN_NAME, $request, $hubReceipt));

        return $response
            ->setPreviousUrl(route('hub-receipt.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(HubReceipt $hubReceipt, Request $request, BaseHttpResponse $response)
    {
        try {
            abort_if(check_user_depent_of_hub($hubReceipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

            $hubReceipt->delete();

            event(new DeletedContentEvent(HUB_RECEIPT_MODULE_SCREEN_NAME, $request, $hubReceipt));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function printQRCode(int|string $id, Request $request)
    {
        try {
            $proBatch = ProductQrcode::where(['reference_id' => $id, 'reference_type' => ProductBatch::class])->first();
            if (!isset($proBatch)) {
                throw new Exception("Không tìm thấy dữ liệu !");
            }
            return view('plugins/hub-warehouse::product-batch-qrcode.qrcode-batch', compact('proBatch'));
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function printQRCodeAll(int|string $id, Request $request)
    {
        try {
            $receipt = HubReceipt::where('id', $id)->first();
            $proBatch = [];
            foreach ($receipt->productBatch as $key => $value) {
                array_push($proBatch, $value->getQRCode);
            }
            if (empty($proBatch)) {
                throw new Exception("Không tìm thấy dữ liệu !");
            }
            return view('plugins/hub-warehouse::product-batch-qrcode.qrcode-batch', compact('proBatch'));
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function getGenerateReceiptProduct(ExportBillRequest $request, HubReceiptHelper $hubReceiptHelper)
    {
        $data = HubReceipt::with('receiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $hubReceiptHelper->streamInvoice($data);
        }
        return $hubReceiptHelper->downloadInvoice($data);
    }
    public function cancel(HubReceipt $receipt, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        $receipt = HubReceipt::where('id', $receipt->id)->sharedLock()->first();

        abort_if(check_user_depent_of_hub($receipt->warehouse_receipt_id), 403, 'Bạn không có quyền thao tác trên đơn này!!!!');

        try {
            if ($receipt->status != ApprovedStatusEnum::PENDING) {
                DB::rollBack();
                return $response
                ->setError()
                ->setMessage('Phiếu đã từ chối....');
            }
            $receipt->status = ApprovedStatusEnum::CANCEL;
            $receipt->reason = $request->input()['reasoon'];
            $receipt->save();
            $receipt->proposal->update([
                'status' => ProposalReceiptProductEnum::REFUSERECEIPT,
                'reason_cancel' => $request->input()['reasoon']
            ]);
            // Create event for admin
            $arrNoti = [
                'action' => 'từ chối',
                'permission' => "hub-receipt.create",
                'route' => route('hub-receipt.view', $receipt->id),
                'status' => 'Đã từ chối'
            ];
            send_notify_cms_and_tele($receipt, $arrNoti);
            DB::commit();
            return $response->setNextUrl(route('hub-receipt.view', $receipt->id))->setMessage('Đã từ chối đơn thành công!!');
        } catch (Exception $exception) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
