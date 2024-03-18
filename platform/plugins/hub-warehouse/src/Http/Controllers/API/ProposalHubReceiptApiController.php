<?php

namespace Botble\HubWarehouse\Http\Controllers\API;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\HubWarehouse\Models\ActualReceiptQrcode;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\ProductQrHistotry;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;


class ProposalHubReceiptApiController extends BaseController
{

    public function printQRCodeForBatch(Request $request)
    {
        $jsonString = file_get_contents('php://input');
        $requestData = json_decode($jsonString, true);
        DB::beginTransaction();

        //Lấy thông tin phiếu nhập kho
        try {
            $receipt = HubReceipt::where('id', $requestData['receipt_id'])->first();
            $arrProductQr = [];

            //Nếu lúc quét QR thực nhận không tồn tại QR của lô
            if (!isset($requestData['batch']['scan_batch_warehouse'])) { //Ghi điều kiện ở đây
                if (!empty($_GET['type']) && $_GET['type'] == 'create-batch') {
                    $total = count($requestData['batch']['qr_ids']['list_id']);

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
                    //Tạo mã QR cho từng lô
                    $dataCreateBatch = [
                        'receipt_id' => $receipt->id,
                        'batch_code' => $batch_code_last,
                        'quantity' => $total,
                        'start_qty' => $total,
                        'status' => ProductBatchStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_receipt_id,
                        'warehouse_type' => Warehouse::class,
                        'product_parent_id' => 0
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
                        'status' => QRStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_receipt_id,
                        'warehouse_type' => Warehouse::class,
                        'reference_id' => $productBatch->id,
                        'created_by' => $requestData['current_user_id'],
                        'reference_type' => ProductBatch::class,
                    ]);

                }

                //Tạo thông tin chi tiết cho từng lô
                foreach ($requestData['batch']['qr_ids']['list_id'] as $key => $proId) {
                    //Dựa vào số lượng của từng sản phẩm để thêm dữ liệu vào
                    $productQRCode = ProductQrcode::where('id', $proId)->first();
                    $product = $productQRCode->reference;

                    if ($_GET['type'] == 'create-batch') {
                        //ví dụ mảng qrcode trả về dạng mảng và được group theo id lô và id sản phẩm. eg: [0=>[10=>['MAQR001','MAQR002],12=>['MMA211]]]
                        $dataCreateBatchDetail = [
                            'batch_id' => $productBatch->id,
                            'product_id' => $product->id,
                            'qrcode_id' => $proId,
                            'product_name' => $product->name,
                            'sku' => $product->sku,
                        ];
                        ProductBatchDetail::query()->create($dataCreateBatchDetail);
                    }

                    //Cập nhật trạng thái của QR khi hoàn tất thực nhập
                    $proQRCode = $productQRCode->update([
                        'status' => QRStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_receipt_id,
                        'warehouse_type' => Warehouse::class,
                        'production_time' => Carbon::today(),
                    ]);

                    //Tạo lịch sử cho từng mã Qr
                    $proQRHistory = ProductQrHistotry::query()->create([
                        'action' => 'receipt_stock',
                        'created_by' => $requestData['current_user_id'],
                        'description' => 'Xác thực nhập kho hub thông qua việc quét mã QR của sản phẩm.',
                        'qrcode_id' => $proId,
                    ]);

                    if (array_key_exists($productQRCode->reference->id, $arrProductQr)) {
                        $arrProductQr[$productQRCode->reference->id]['qty'] += 1;
                    } else {
                        $arrProductQr[$productQRCode->reference->id]['qty'] = 1;
                    }
                    $arrProductQr[$productQRCode->reference->id]['data'] = $productQRCode->reference;

                    //Lưu lại thông tin các qr được quét của phiếu nhập này
                    ActualReceiptQrcode::query()->create([
                        'receipt_id' => $receipt->id,
                        'product_id' => $product->id,
                        'qrcode_id' => $proId,
                        'batch_id' => isset($productBatch) ? $productBatch->id : null,
                    ]);
                }
            } else { //Cập nhật trạng thái của QR lô đó
                for ($i = 0; $i < count($requestData['batch']['batch_id']); $i++) {
                    $batchId = $requestData['batch']['batch_id'][$i];

                    $productBatch = ProductBatch::where('id', $batchId)->first();
                    $productBatch->update([
                        'status' => ProductBatchStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_receipt_id,
                        'warehouse_type' => Warehouse::class,
                    ]);
                    event(new UpdatedContentEvent(PRODUCT_BATCH_MODULE_SCREEN_NAME, $request, $productBatch));

                    //Update QR status
                    $productBatchQR = ProductQrcode::where([
                        'reference_id' => $batchId,
                        'reference_type' => ProductBatch::class
                    ])->first();
                    $productBatchQR->update([
                        'status' => QRStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_receipt_id,
                        'warehouse_type' => Warehouse::class,
                    ]);

                    //Cập nhật trạng thái QR từng sản phẩm trong lô
                    foreach ($productBatch->productInBatch as $key => $proItem) {
                        $proItem->statusQrCode->update([
                            'status' => QRStatusEnum::INSTOCK,
                            'warehouse_id' => $receipt->warehouse_receipt_id,
                            'warehouse_type' => Warehouse::class,
                        ]);

                        if (array_key_exists($proItem->product_id, $arrProductQr)) {
                            $arrProductQr[$proItem->product_id]['qty'] += 1;
                        } else {
                            $arrProductQr[$proItem->product_id]['qty'] = 1;
                        }
                        $arrProductQr[$proItem->product_id]['data'] = $proItem->product;

                         //Lưu lại thông tin các qr được quét của phiếu nhập này
                        ActualReceiptQrcode::query()->create([
                            'receipt_id' => $receipt->id,
                            'product_id' => $proItem->product_id,
                            'qrcode_id' => $proItem->statusQrCode->id,
                            'batch_id' => $productBatch->id,
                        ]);
                    }
                }
            }

            DB::commit();

            if (!isset($requestData['batch']['scan_batch_warehouse'])) {
                // Trả về view dưới dạng response JSON
                if ($_GET['type'] == 'create-batch') {
                    return response()->json(['view' => view('plugins/hub-warehouse::product-batch-qrcode.qrcode-batch', compact('proBatch'))->render(), 'batch' => $productBatch, 'batchDetail' => $productBatch->productInBatch, 'receiptDetail' => $receipt->receiptDetail->groupBy('product_id'), 'listProduct' => $arrProductQr]);
                }
                return $this->responseApi(0, 'Nhập sản phẩm cho kho thành công!!', 200, $arrProductQr);
            } else {
                return $this->responseApi(0, 'Nhập lô hàng cho kho thành công!!', 200, $arrProductQr);
            }
        } catch (Exception $err) {
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }
    }

    public function getListCreatedShipment(string|int $id)
    {
        try {

            $batch = ActualReceiptQrcode::query()->where([
                'receipt_id' => $id
            ])->withWhereHas('batch')->get();

            $stamp = [];

            foreach ($batch as $key => $value) {
                foreach ($value->batch->productInBatch as $key => $detail) {
                    # code...
                    if(!array_key_exists($detail->product_id, $stamp)){
                        $stamp[$detail->product_id] = $detail;
                    }
                }
            }

            $receipt = HubReceipt::where('id', $id)->first();

            $productHasScan = ActualReceiptQrcode::query()->where(['receipt_id' => $id])->get();

            $qtyPro = [];

            if (!empty($productHasScan)) {
                foreach ($productHasScan as $key => $value) {
                    # code...
                    if (array_key_exists($value->product_id, $qtyPro)) {
                        $qtyPro[$value->product_id]['qty'] += 1;
                    } else {
                        $qtyPro[$value->product_id]['qty'] = 1;
                    }
                    $qtyPro[$value->product_id]['data'] = $value->productQrcode->reference;
                }
            }


        } catch (Exception $err) {
            throw new Exception($err->getMessage(), 1);
        }

        if (!empty($receipt)) {
            return $this->responseApi(0, "Thành công!", 200, ['batch' => $stamp ?? null, 'qtyPro' => $qtyPro]);
        } else {
            return $this->responseApi(1, "Không tìm thấy phiếu nhập kho này!", 200);
        }
    }

    public function responseApi($errorCode, $msg, $httpCode, $data = null)
    {
        $dataRes = [
            'error_code' => $errorCode,
            'msg' => $msg,
            'body' => $data,
        ];

        return response()->json($dataRes, $httpCode);
    }
}
