<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers\API;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Warehouse\Enums\BaseStatusEnum;
use Botble\Warehouse\Models\ProcessingHouse;
use Botble\WarehouseFinishedProducts\Enums\BatchDetailStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ActualReceiptQrcode;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\ProductBatchQrCode;
use Botble\WarehouseFinishedProducts\Models\ProductQrHistotry;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;


class ProposalGoodsApiController extends BaseController
{
    //Lấy toàn bộ sản phẩm có trong mỗi kho thành phẩm/phế phẩm
    public function getProductsInStock()
    {
        $warehouse = WarehouseFinishedProducts::where(['status' => BaseStatusEnum::PUBLISHED])->with('products', function($q){
            return $q->with('variationProductAttributes');
        })->get();

        if(!empty($warehouse)){
            return $this->responseApi(0, "Thành công!", 200, $warehouse);
        }else{
            return $this->responseApi(1, "Không tìm thấy bất kỳ sản phẩm nào trong kho!", 200);
        }
    }

    //Lấy toàn bộ thông tin nhà gia công
    public function getAllProcessingHourse()
    {
        $processingHourse = ProcessingHouse::where(['status' => HubStatusEnum::ACTIVE])->get();
        if(!empty($processingHourse)){
            return $this->responseApi(0, "Thành công!", 200, $processingHourse);
        }else{
            return $this->responseApi(1, "Không tìm thấy bất kỳ nhà gia công nào!", 200);
        }
    }

    //Lấy toàn bộ thông tin sản phẩm
    public function getAllProducts()
    {
        $products = Product::select('id', 'is_variation', 'name', 'quantity', 'sku', 'status')->where(['status' => BaseStatusEnum::PUBLISHED, 'is_variation' => 1])->with('variationProductAttributes')->limit(10)->get();

        if(!empty($products)){
            return $this->responseApi(0, "Thành công!", 200, $products);
        }else{
            return $this->responseApi(1, "Không tìm thấy bất kỳ sản phẩm nào!", 200);
        }
    }

    //Lấy thông tin đơn đề xuất nhập kho khi chỉnh sửa
    public function getInfoProposalReceiptProductById(string|int $id)
    {
        $proposal = ProposalReceiptProducts::where(['id' => $id])->with('proposalDetail')->first();

        if(!empty($proposal)){
            return $this->responseApi(0, "Thành công!", 200, $proposal);
        }else{
            return $this->responseApi(1, "Không tìm thấy đơn đề xuất nhập kho này!", 200);
        }
    }

    public function checkValidatePurchaseGoods()
    {
        $requestData = json_decode($_POST['data']);
        $expectedDate = Carbon::createFromFormat('Y-m-d', $requestData->expected_date)->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        if(empty($requestData->title)){
            return $this->responseApi(1, "Vui lòng nhập tiêu đề đơn!", 200);
        }

        if(($expectedDate) < $currentDate){
            return $this->responseApi(1, "Vui lòng nhập ngày dự kiến lớn hơn ngày hiện tại!", 200);
        }

        $parameter = '';

        if($requestData->type_proposal == 'stock')
        {
            $parameter = 'stock';

            if(!isset($requestData->stock->detination_wh_id)){
                return $this->responseApi(1, "Vui lòng chọn kho lấy sản phẩm!", 200);
            }

            if(($requestData->stock->detination_wh_id) == $requestData->warehouse_id){
                return $this->responseApi(1, "Kho xuất sản phẩm và kho đích không được trùng!", 200);
            }
        }else if($requestData->type_proposal == 'inventory'){//
            $parameter = 'inventory';
        }else if($requestData->type_proposal == 'stock-odd'){//
            $parameter = 'stock-odd';

            if(!isset($requestData->{'stock-odd'}->detination_wh_id)){
                return $this->responseApi(1, "Vui lòng chọn kho lấy sản phẩm!", 200);
            }

            if(($requestData->{'stock-odd'}->detination_wh_id) == $requestData->warehouse_id){
                return $this->responseApi(1, "Kho xuất sản phẩm và kho đích không được trùng!", 200);
            }
        }

        foreach ($requestData->$parameter->product as $key => $value) {
            if(empty($value->quantity))
            {
                return $this->responseApi(1, "Vui lòng nhập đầy đủ số lượng nhập sản phẩm!", 200);
            }

            if((int)($value->quantity) < 1){
                return $this->responseApi(1, "Vui lòng nhập số lượng lớn hơn hoặc bằng 1!", 200);
            }

            if(empty($value->sku))
            {
                return $this->responseApi(1, "Mục SKU của sản phẩm không được phép rỗng!", 200);
            }
        }

        return $this->responseApi(0, "Đang tạo đơn!", 200);
    }

    public function printQRCodeForBatch(Request $request){
        $jsonString = file_get_contents('php://input');
        $requestData = json_decode($jsonString, true);
        DB::beginTransaction();

        //Lấy thông tin phiếu nhập kho
        try{
            $receipt = ReceiptProduct::where('id', $requestData['receipt_id'])->first();
            $arrProductQr = [];

            //Nếu lúc quét QR thực nhận không tồn tại QR của lô
            if(!isset($requestData['batch']['scan_batch_warehouse'])){//Ghi điều kiện ở đây
                if(!empty($_GET['type']) && $_GET['type'] == 'create-batch'){
                    $parent_product_id = $requestData['batch']['qr_ids']['parent_id'];//$requestData['batch']['qr_ids']['parent_id']
                    $total = count($requestData['batch']['qr_ids']['list_id']);
    
                    $prefix = "BAT-WFP";
    
                    $lastBatch = ProductBatch::orderByDesc('id')->first();
                    if(empty($lastBatch)){
                        $lastProductBatch = 1;
                        $batch_code = str_pad($lastProductBatch, 7, '0', STR_PAD_LEFT);
                    }else{
                        $productBatch = (int) substr($lastBatch->batch_code, 7);
                        $batch_code = str_pad($productBatch+1, 7, '0', STR_PAD_LEFT);
                    }
    
                    $batch_code_last = $prefix . $batch_code;
    
                    // Tạo mã QR cho từng lô
                    $dataCreateBatch = [
                        'receipt_id' => $receipt->id,
                        'batch_code' => $batch_code_last,
                        'quantity' => $total,
                        'start_qty' => $total,
                        'status' => ProductBatchStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_type' => WarehouseFinishedProducts::class,
                        'product_parent_id' => 0
                    ];
                    $productBatch = ProductBatch::query()->create($dataCreateBatch);
    
                    $randomString = Str::random(7);
                    $dateTimeNow = Carbon::now()->format('ymdHis');
                    $qrCode = $randomString . $dateTimeNow;
                    $qrCodeWithLogo = QrCode::size(150)->format('png')->errorCorrection('H')->generate($qrCode);
    
                    $proBatch = ProductQrcode::query()->create([
                        'qr_code' => $qrCode,
                        'base_code_64' => base64_encode($qrCodeWithLogo),
                        'status' => QRStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_type' => WarehouseFinishedProducts::class,
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

                    if($_GET['type'] == 'create-batch'){
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
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_type' => WarehouseFinishedProducts::class
                    ]);
                    
                    if(array_key_exists($productQRCode->reference->id, $arrProductQr)){
                        $arrProductQr[$productQRCode->reference->id]['qty'] += 1;
                    }else{
                        $arrProductQr[$productQRCode->reference->id]['qty'] = 1;
                    }
                    $arrProductQr[$productQRCode->reference->id]['data'] = $productQRCode->reference;

                    //Tạo lịch sử cho từng mã Qr
                    $proQRHistory = ProductQrHistotry::query()->create([
                        'action' => 'receipt_stock',
                        'created_by' => $requestData['current_user_id'],
                        'description' => 'Nhập kho thành phẩm.',
                        'qrcode_id' => $proId,
                    ]);

                    //Lưu lại thông tin các qr được quét của phiếu nhập này
                    ActualReceiptQrcode::query()->create([
                        'receipt_id' => $receipt->id,
                        'product_id' => $product->id,
                        'qrcode_id' => $proId,
                        'is_batch' => $_GET['type'] == 'create-batch' ? 1 : 0
                    ]);
                }
            }
            else{//Cập nhật trạng thái của QR lô đó
                for ($i=0; $i < count($requestData['batch']['batch_id']); $i++) {
                    $batchId = $requestData['batch']['batch_id'][$i];

                    $productBatch = ProductBatch::where('id', $batchId)->first();

                    $productBatch->update([
                        'status' => ProductBatchStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_type' => WarehouseFinishedProducts::class,
                    ]);
                    event(new UpdatedContentEvent(PRODUCT_BATCH_MODULE_SCREEN_NAME, $request, $productBatch));

                    //Update QR status
                    $productBatchQR = ProductQrcode::where([
                        'reference_id' => $batchId,
                        'reference_type' => ProductBatch::class
                    ])->first();
                    $productBatchQR->update([
                        'status' => QRStatusEnum::INSTOCK,
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_type' => WarehouseFinishedProducts::class,
                    ]);

                    //Cập nhật trạng thái QR từng sản phẩm trong lô
                    foreach ($productBatch->productInBatch as $key => $proItem) {
                        $proItem->statusQrCode->update([
                            'status' => QRStatusEnum::INSTOCK,
                        ]);
                    }
                }
            }

            DB::commit();

            if(!isset($requestData['batch']['scan_batch_warehouse'])){
                // Trả về view dưới dạng response JSON
                if($_GET['type'] == 'create-batch'){
                    return response()->json(['view' => view('plugins/warehouse-finished-products::product-batch-qrcode.qrcode-batch', compact('proBatch'))->render(), 'batch' => $productBatch, 'batchDetail' => $productBatch->productInBatch, 'receiptDetail' => $receipt->receiptDetail->groupBy('product_id'), 'listProduct' => $arrProductQr]);
                }
                return $this->responseApi(0, 'Nhập sản phẩm cho kho thành công!!', 200, $arrProductQr);
            }else{
                return $this->responseApi(0, 'Nhập lô hàng cho kho thành công!!', 200);
            }
        }catch(Exception $err){
            DB::rollBack();
            throw new Exception($err->getMessage(), 1);
        }

        
    }

    public function getListCreatedShipment(string|int $id){
        try{
            $batch = ProductBatch::where([
                'receipt_id' => $id,
                'status' => ProductBatchStatusEnum::INSTOCK,
                'warehouse_type' => WarehouseFinishedProducts::class,
            ])->with('productInBatch')->get();
            $receipt = ReceiptProduct::where('id', $id)->first();
            
            $productHasScan = ActualReceiptQrcode::query()->where(['receipt_id' => $id])->get();
            
            $qtyPro = [];
            
            if(!empty($productHasScan)){
                foreach ($productHasScan as $key => $value) {
                    # code...
                    if(array_key_exists($value->product_id, $qtyPro)){
                        $qtyPro[$value->product_id]['qty'] += 1;
                    }else{
                        $qtyPro[$value->product_id]['qty'] = 1;
                    }
                    $qtyPro[$value->product_id]['data'] = $value->productQrcode->reference;
                }
            }
        }catch(Exception $err){
            throw new Exception($err->getMessage(), 1);
        }
        
        if(!empty($receipt)){
            return $this->responseApi(0, "Thành công!", 200, ['batch' => $batch ?? null, 'qtyPro' => $qtyPro]);
        }else{
            return $this->responseApi(1, "Không tìm thấy phiếu nhập kho này!", 200);
        }
    }

    //Lấy toàn bộ sản phẩm cha của lô hàng trong kho
    public function getParentProductInWarehouse()
    {
        $collection = [];

        $warehouseOfBatch = ProductBatch::where([
            'warehouse_type' => WarehouseFinishedProducts::class,
            'status' => ProductBatchStatusEnum::INSTOCK
        ])->get()->groupBy('warehouse_id');

        foreach ($warehouseOfBatch as $warehouse_id => $warehouse) {
            $batchs = ProductBatch::where([
                'warehouse_type' => WarehouseFinishedProducts::class,
                'warehouse_id' => $warehouse_id,
                'status' => ProductBatchStatusEnum::INSTOCK
            ])->with('product')->get();
            # code...
            $batchsGroup = $batchs->groupBy('product_parent_id');
            foreach ($batchsGroup as $product_parent_id => $parentProduct) {
                $collection[$warehouse_id][$product_parent_id] = [
                    'quantity' => $parentProduct->count(),
                    'data' => $parentProduct->where('product_parent_id',$product_parent_id)->first()
                ];
            }
        }

        if(!empty($collection)){
            $dataRes = [
                'error_code' => 0,
                'msg' => 'Thành công!',
                'body' => $collection,
            ];
            return response()->json($dataRes, 200, [], JSON_FORCE_OBJECT);
        }else{
            return $this->responseApi(1, "Không tìm thấy bất kỳ lô hàng nào trong kho!", 200);
        }
    }

    public function getAllProductParent(){
        try{
            $products = Product::select('id', 'is_variation', 'name', 'quantity', 'sku', 'status', 'images')->where(['status' => BaseStatusEnum::PUBLISHED, 'is_variation' => 0])->get();
    
            if(!empty($products)){
                return $this->responseApi(0, "Thành công!", 200, $products);
            }else{
                return $this->responseApi(1, "Không tìm thấy bất kỳ sản phẩm nào!", 200);
            }
        }catch(Exception $e){
            throw new $e;
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
