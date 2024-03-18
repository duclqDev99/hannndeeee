<?php

namespace Botble\SaleWarehouse\Http\Controllers;

use Botble\SaleWarehouse\Http\Requests\SaleReceiptRequest;
use Botble\SaleWarehouse\Models\SaleReceipt;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\SaleWarehouse\Tables\SaleReceiptTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\SaleWarehouse\Forms\SaleReceiptForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\SaleWarehouse\Models\SaleActualReceipt;
use Botble\SaleWarehouse\Models\SaleActualReceiptDetail;
use Botble\SaleWarehouse\Models\SaleProduct;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReceiptController extends BaseController
{




    public function index(SaleReceiptTable $table)
    {

        PageTitle::setTitle(trans('Danh sách kho'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sale-warehouse::sale-receipt.create'));

        return $formBuilder->create(SaleReceiptForm::class)->renderForm();
    }

    public function store(SaleReceiptRequest $request, BaseHttpResponse $response)
    {
        $saleReceipt = SaleReceipt::query()->create($request->input());

        event(new CreatedContentEvent(SALE_RECEIPT_MODULE_SCREEN_NAME, $request, $saleReceipt));

        return $response
            ->setPreviousUrl(route('sale-receipt.index'))
            ->setNextUrl(route('sale-receipt.edit', $saleReceipt->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(SaleReceipt $saleReceipt, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $saleReceipt->name]));

        return $formBuilder->create(SaleReceiptForm::class, ['model' => $saleReceipt])->renderForm();
    }

    public function update(SaleReceipt $saleReceipt, SaleReceiptRequest $request, BaseHttpResponse $response)
    {
        $saleReceipt->fill($request->input());

        $saleReceipt->save();

        event(new UpdatedContentEvent(SALE_RECEIPT_MODULE_SCREEN_NAME, $request, $saleReceipt));

        return $response
            ->setPreviousUrl(route('sale-receipt.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(SaleReceipt $saleReceipt, Request $request, BaseHttpResponse $response)
    {
        try {
            $saleReceipt->delete();

            event(new DeletedContentEvent(SALE_RECEIPT_MODULE_SCREEN_NAME, $request, $saleReceipt));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function confirmView(int|string $id, BaseHttpResponse $response)
    {
        $productIssue = SaleReceipt::find($id);
        PageTitle::setTitle(trans('Phiếu nhập kho :name', ['name' => get_proposal_receipt_product_code($productIssue->receipt_code)]));
        $warehouseReceiptExists = $productIssue->whereIn('warehouse_receipt_id', get_list_sale_warehouse_id_for_current_user())->exists();
        if (Auth::user()->hasPermission('sale_warehouse.all') || $warehouseReceiptExists) {
            if ($productIssue->status->toValue() !== ApprovedStatusEnum::PENDING) {
                return $response
                    ->setError()
                    ->setMessage(__('Đơn hàng đã nhập kho'));
            }
            return view('plugins/sale-warehouse::receipt.confirm', compact('productIssue'));
        }
        return $response
            ->setError()
            ->setMessage('Không có quyền truy cập');
    }

    public function confirm($id, Request $request, BaseHttpResponse $response)
    {
        $saleReceipt = SaleReceipt::where('id', $id)->sharedLock()->first();


        if ($saleReceipt->status == ApprovedStatusEnum::PENDING) {
            DB::beginTransaction();
            try {
                $saleReceipt->status = ApprovedStatusEnum::APPOROVED;
                $saleReceipt->invoice_confirm_name = Auth::user()->name;
                $saleReceipt->date_confirm = Carbon::now();
                $saleReceipt->save();

                $filteredImages = array_filter($request->input('images'));
                $imageJson = json_encode($filteredImages);
                $dataActual = [
                    'receipt_id' => $saleReceipt->id,
                    'image' => $imageJson
                ];
                $saleActual = SaleActualReceipt::query()->create($dataActual);
                foreach ($saleReceipt->receiptDetail as $receiptDetail) {
                    $batch = ProductBatch::find($receiptDetail->batch_id);
                    if ($batch) {
                        $batch->update([
                            'status' => ProductBatchStatusEnum::INSTOCK,
                            'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                            'warehouse_type' => SaleWarehouseChild::class,
                        ]);
                        foreach ($batch->productInBatch as $productBatch) {
                            $saleProduct = SaleProduct::where(
                                [
                                    'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                                    'product_id' => $productBatch->product_id
                                ]
                            )->first();
                            if ($saleProduct) {
                                $saleProduct->increment('quantity');
                            } else {
                                $saleProduct = SaleProduct::create([
                                    'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                                    'product_id' => $productBatch->product_id,
                                    'quantity' => 1
                                ]);
                            }
                            $productBatch->statusQrCode->update([
                                'warehouse_type' => SaleWarehouseChild::class,
                                'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                                'status' => QRStatusEnum::INSTOCK,
                            ]);
                            $product = Product::where('id', $productBatch->product_id)->first();
                            $dataInsertActualDetail = [
                                'actual_id' => $saleActual->id,
                                'product_id' => $productBatch->product_id,
                                'product_name' => $product->name,
                                'sku' => $product->sku,
                                'quantity' => 1,
                                'batch_id' => $batch->id,
                                'qrcode_id' => $productBatch->statusQrCode->id
                            ];
                            SaleActualReceiptDetail::create($dataInsertActualDetail);
                        }
                        $qrcode = $batch->getQRCode;
                        if ($qrcode) {
                            $qrcode->update([
                                'status' => QRStatusEnum::INSTOCK,
                                'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                                'warehouse_type' => SaleWarehouseChild::class,
                            ]);
                        }
                    } else {
                        $this->handleNonexistentBatch($receiptDetail, $saleReceipt, $saleActual);
                    }
                }

                $arrNoti = [
                    'action' => 'đã nhập',
                    'permission' => "sale-receipt.index",
                    'route' => route('sale-receipt.index'),
                    'status' => 'đã nhập'
                ];
                send_notify_cms_and_tele($saleReceipt, $arrNoti);
                DB::commit();
                return $response
                    ->setNextUrl(route('sale-receipt.index'))
                    ->setMessage(trans('Đã cập nhật'));
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

    public function denied($id, Request $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        $saleReceipt = SaleReceipt::where('id', $id)->sharedLock()->first();
        if ($saleReceipt->status == ApprovedStatusEnum::CANCEL) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage('Đơn đã từ chối');
        } else {
            try {

                $saleReceipt->status = ApprovedStatusEnum::CANCEL;
                $saleReceipt->reason_cancel = $request->input('denyReason');
                $saleReceipt->save();

                $arrNoti = [
                    'action' => 'từ chối',
                    'permission' => "sale-receipt.index",
                    'route' => route('sale-receipt.index'),
                    'status' => 'từ chối'
                ];
                send_notify_cms_and_tele($saleReceipt, $arrNoti);
                DB::commit();
                return $response->setPreviousUrl(route('sale-receipt.index'))
                    ->setNextUrl(route('sale-receipt.index'))->setMessage(trans('Từ chối duyệt đơn'));
            } catch (Exception $e) {
                DB::rollBack();
                return $response
                    ->setError()
                    ->setMessage($e->getMessage());
            }
        }
    }
    private function handleNonexistentBatch($receiptDetail, $saleReceipt, $saleActual)
    {

        $product = Product::find($receiptDetail->product_id);
        $saleProduct = SaleProduct::where(
            [
                'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                'product_id' => $receiptDetail->product_id
            ]
        )->first();
        if ($saleProduct) {
            $saleProduct->increment('quantity');
        } else {
            $saleProduct = SaleProduct::create([
                'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                'product_id' => $receiptDetail->product_id,
                'quantity' => 1
            ]);
        }

        $qrcode = ProductQrcode::find($receiptDetail->qrcode_id);
        $dataInsertActualDetail = [
            'actual_id' => $saleActual->id,
            'product_id' => $receiptDetail->product_id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'qrcode_id' => $receiptDetail->qrcode_id
        ];
        SaleActualReceiptDetail::create($dataInsertActualDetail);

        if ($qrcode) {
            $qrcode->update([
                'status' => QRStatusEnum::INSTOCK,
                'warehouse_id' => $saleReceipt->warehouse_receipt_id,
                'warehouse_type' => SaleWarehouseChild::class,
            ]);
        }
    }
    public function view($id, BaseHttpResponse $response)
    {
        PageTitle::setTitle('Thông tin nhập kho');
        $productIssue = SaleReceipt::find($id);
        if ($productIssue) {
            $userHasPermission = Auth::user()->hasPermission('sale_warehouse.all');
            $warehouseReceiptExists = $productIssue->whereIn('warehouse_receipt_id', get_list_sale_warehouse_id_for_current_user())->exists();
            if ($userHasPermission || $warehouseReceiptExists) {
                $actualIssueHub = $productIssue?->hubIssue?->actualIssue;
                $batchs = $productIssue?->hubIssue?->actualQrCode;
                $actualIssue = SaleActualReceipt::where('receipt_id', $id)->with('actualDetail')->first();
                return view('plugins/sale-warehouse::receipt.view', compact('productIssue', 'actualIssue', 'actualIssueHub', 'batchs'));
            }

            return $response
                ->setError()
                ->setNextUrl(route('sale-receipt.index'))
                ->setMessage('Không có quyền truy cập');

        }
        return $response
            ->setError()
            ->setNextUrl(route('sale-receipt.index'))
            ->setMessage('Không tồn tại phiếu nhập kho');
    }
}
