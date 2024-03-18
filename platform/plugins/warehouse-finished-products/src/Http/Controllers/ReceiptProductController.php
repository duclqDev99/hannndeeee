<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\PageTitle;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Illuminate\Http\Request;
use Exception;
use Botble\WarehouseFinishedProducts\Tables\ReceiptProductTable;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\ExportBillRequest;
use Botble\WarehouseFinishedProducts\Models\ActualReceipt;
use Botble\WarehouseFinishedProducts\Models\ActualReceiptBatch;
use Botble\WarehouseFinishedProducts\Models\ActualReceiptDetail;
use Botble\WarehouseFinishedProducts\Models\ActualReceiptQrcode;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\ProductBatchQrCode;
use Botble\WarehouseFinishedProducts\Models\ProductQrHistotry;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Botble\WarehouseFinishedProducts\Supports\ReceiptProductHelper;
use Botble\Widget\Events\RenderingWidgetSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class ReceiptProductController extends BaseController
{
    private $encryptionKey;

    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Phiếu nhập kho'), route('receipt-product.index'));

        $this->encryptionKey = env('ENCRYPTION_QR_KEY');
    }
    public function index(ReceiptProductTable $table)
    {

        return $table->renderTable();
    }

    public function censorshipReceiptProduct(ReceiptProduct $receipt)
    {
        abort_if($receipt->status == ApprovedStatusEnum::APPOROVED, 403);
        $receipt = $this->applyWarehouseFilter($receipt);
        PageTitle::setTitle(__('Xác nhận nhập kho'));

        Assets::addScripts(['sortable'])
            ->addScriptsDirectly(
                [
                    'vendor/core/plugins/warehouse-finished-products/js/batch.js',
                    'vendor/core/plugins/gallery/js/gallery-admin.js',
                ]
            )->addStylesDirectly('vendor/core/packages/widget/css/widget.css');

        RenderingWidgetSettings::dispatch();
        return view('plugins/warehouse-finished-products::receipt-product.batch', compact('receipt'));
    }

    public function approvedReceiptProduct(ReceiptProduct $receipt, Request $request, BaseHttpResponse $response)
    {
        try {
            $requestData = $request->input();

            $listProductScan = ActualReceiptQrcode::query()->where(['receipt_id' => $receipt->id])->get();
            $totalQuantity = $listProductScan->count();
            //Tính tổng số lượng sản phẩm đã nhập vào từng lô

            DB::beginTransaction();

            $receipt->update([
                'status' => ApprovedStatusEnum::APPOROVED,
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => Carbon::now()->format('Y-m-d'),
            ]);

            if(!empty($receipt->proposal)){
                $receipt->proposal->update([
                    'status' => ProposalProductEnum::APPOROVED,
                    'invoice_confirm_name' => Auth::user()->name,
                    'date_confirm' => Carbon::now()->format('Y-m-d'),
                ]);
            }

            // Insert actual
            $dataInsertActual = [
                'receipt_id' => $receipt->id,
                'general_order_code' => $receipt->general_order_code,
                'warehouse_id' => $receipt->warehouse_id,
                'warehouse_name' => $receipt->warehouse_name,
                'warehouse_address' => $receipt->warehouse_address,
                'invoice_confirm_name' => Auth::user()->name,
                'quantity' => $totalQuantity,
                'wh_departure_id' => $receipt->wh_departure_id,
                'wh_departure_name' => $receipt->wh_departure_name,
                'status' => ApprovedStatusEnum::APPOROVED,
                'image' => $requestData['gallery']
            ];

            $actual = ActualReceipt::query()->create($dataInsertActual);

            // Create event for admin
            $arrNoti = [
                'action' => 'xác nhận',
                'permission' => "proposal-receipt-products.censorship",
                'route' => route('receipt-product.view', $receipt->id),
                'status' => 'Đã nhập kho'
            ];
            send_notify_cms_and_tele($receipt, $arrNoti);

            //Tạo 1 mảng chứa id của mỗi sản phẩm riêng biệt và tổng số lượng của chúng để tạo chi tiết cho đơn thực nhập
            $arrProductActual = [];

            foreach ($listProductScan as $key => $item) {
                $product = $item->product;

                $stockBy = QuantityProductInStock::where(['stock_id' => $receipt->warehouse_id, 'product_id' => $item->product_id])->first();
                if (!empty($stockBy)) {
                    $qty = (int) $stockBy->quantity + 1;
                    $stockBy->update(['quantity' => $qty]);
                } else {
                    $dataInsert = [
                        'stock_id' => $receipt->warehouse_id,
                        'product_id' => $item->product_id,
                        'quantity' => 1,
                    ];
                    QuantityProductInStock::create($dataInsert);
                }

                //Cập nhật số lượng cho sản phảm thương mại điện tử
                $product->update([
                    'quantity' => (int) $product->quantity + 1,
                ]);
                new UpdatedContentEvent(PRODUCT_MODULE_SCREEN_NAME, $request, $product);

                //Thêm dữ liệu vào mảng chi tiết thực nhập sản phẩm
                if (array_key_exists($item->product_id, $arrProductActual)) {
                    $arrProductActual[$item->product_id] += 1;
                } else {
                    $arrProductActual[$item->product_id] = 1;
                }
            }

            $productBatch = ProductBatch::where([
                'receipt_id' => $receipt->id,
                'warehouse_type' => WarehouseFinishedProducts::class
            ])->get();
            
            foreach ($productBatch as $key => $values){
                //Lưu lại lịch sử các lô đã tạo của đơn
                ActualReceiptBatch::create([
                    'actual_id' => $actual->id,
                    'batch_id' => $values->id,
                    'quantity' => $values->quantity,
                    'start_qty' => $values->start_qty,
                ]);
            }

            //Tạo chi tiết cho đơn thực nhập
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
            throw new Exception($err->getMessage(), 1);
        }
        //DB commit
        DB::commit();

        return $response
            ->setPreviousUrl(route('receipt-product.index'))
            ->setNextUrl(route('receipt-product.view', $receipt))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function viewReceiptProduct(ReceiptProduct $receipt)
    {
        $receipt = $this->applyWarehouseFilter($receipt);
        $actual = ActualReceipt::where(['receipt_id' => $receipt->id])->with('actualDetail')->first();

        PageTitle::setTitle('Thông tin nhập kho thành phẩm');

        Assets::addScripts(['sortable'])
            ->addScriptsDirectly(
                [
                    'vendor/core/plugins/gallery/js/gallery-admin.js',
                    'vendor/core/plugins/warehouse-finished-products/js/print-batch-qrcode.js',
                    "https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js",
                    'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js'
                ]
            );

        $batchs = $actual->batchs;

        return view('plugins/warehouse-finished-products::receipt-product/view', compact('receipt', 'actual', 'batchs'));
    }

    public function getGenerateReceiptProduct(ExportBillRequest $request, ReceiptProductHelper $receiptHelper)
    {
        $data = ReceiptProduct::with('receiptDetail')->find($request->id);

        if ($request->button_type === 'print') {
            return $receiptHelper->streamInvoice($data);
        }
        return $receiptHelper->downloadInvoice($data);
    }

    //giải mã

    public function printQRCode(int|string $id, Request $request)
    {
        try {
            $proBatch = ProductQrcode::where(['reference_id' => $id, 'reference_type' => ProductBatch::class])->first();
            if (!isset($proBatch)) {
                throw new Exception("Không tìm thấy dữ liệu !");
            }
            return view('plugins/warehouse-finished-products::product-batch-qrcode.qrcode-batch', compact('proBatch'));
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function printQRCodeAll(int|string $id, Request $request)
    {
        try {
            $receipt = ReceiptProduct::where('id', $id)->first();
            $receipt = $this->applyWarehouseFilter($receipt);
            $proBatch = [];
            foreach ($receipt->productBatch as $key => $value) {
                array_push($proBatch, $value->getQRCode);
            }
            if (empty($proBatch)) {
                throw new Exception("Không tìm thấy dữ liệu !");
            }
            return view('plugins/warehouse-finished-products::product-batch-qrcode.qrcode-batch', compact('proBatch'));
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function ajaxPostQrScan(Request $request)
    {
        try {
            $qrDecrypt = $request->qr_code;
            $productQrCode = $qrDecrypt ? ProductQrcode::where(['qr_code' => $qrDecrypt])->first() : false;
            if (!$productQrCode)
                throw new \Exception('Mã QR không tồn tại trên hệ thống');

            return response()->json([
                'success' => 1,
                'message' => 'Quét thành công',
                'data' => $productQrCode->loadMissing([
                    'warehouse',
                    'reference',
                ]),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function ajaxGetBatchInfo(Request $request)
    {
        $batch_id = $request->batch_id;
        $batch = ProductBatch::with([
            'productInBatch.product.variationProductAttributes',
        ])->find($batch_id);
        return view('plugins/warehouse-finished-products::product-issue.ajax.batch-info', compact('batch'));
    }

    public function cancelReceiptProduct(ReceiptProduct $receipt, Request $request, BaseHttpResponse $response)
    {
        try {
            $receipt->status = ApprovedStatusEnum::CANCEL;
            $receipt->reasoon = $request->input()['reasoon'];
            $receipt->save();
            new UpdatedContentEvent(RECEIPT_PRODUCT_MODULE_SCREEN_NAME, $request, $receipt);

            // Create event for admin
            $arrNoti = [
                'action' => 'từ chối',
                'permission' => "receipt-product.censorship",
                'route' => route('receipt-product.view', $receipt->id),
                'status' => 'Đã từ chối'
            ];
            send_notify_cms_and_tele($receipt, $arrNoti);

            return $response->setNextUrl(route('receipt-product.index'))->setMessage('Đã từ chối đơn thành công!!');
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    function applyWarehouseFilter($receiptProduct)
    {
        if (!request()->user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            if (!in_array($receiptProduct->warehouse_id, $warehouseIds)) {
                abort(403, 'Không có quyền truy cập đơn hàng');
            }
        }
        return $receiptProduct;
    }
}
