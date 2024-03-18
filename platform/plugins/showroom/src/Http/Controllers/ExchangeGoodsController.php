<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Enums\ExchangeGoodsStatusEnum;
use Botble\Showroom\Http\Requests\ExchangeGoodsRequest;
use Botble\Showroom\Models\ExchangeGoods;
use Botble\Showroom\Models\ExchangeGoodsDetail;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Tables\ExchangeGoodsTable;
use Botble\Showroom\Tables\ShowroomTable;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExchangeGoodsController extends BaseController
{
    private $pageTitle;

    public function __construct()
    {
        $this->pageTitle = trans('plugins/showroom::showroom.page_title');
    }

    public function index(ExchangeGoodsTable $table)
    {

        return $table->renderTable();
    }

    public function create()
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/showroom/js/exchange-goods.js',
            'vendor/core/plugins/showroom/js/scanner.js',
            'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js'
        ])
        ->addStylesDirectly([
            'vendor/core/plugins/showroom/css/exchange-goods.css'
        ])
        ->addScripts(['input-mask','sortable']);

        Assets::usingVueJS();

        $this->pageTitle(trans('plugins/sales::orders.create'));

        return view('plugins/showroom::exchange-goods.create');
    }

    public function ajaxPostQrScan(Request $request)
    {
        try {
            $qrDecrypt = $request->qr_code;
            $productQrCode = $qrDecrypt ? ProductQrcode::where('qr_code', $qrDecrypt)->first() : false;
            if (!$productQrCode) throw new \Exception('Mã QR không tồn tại trên hệ thống');

            if($productQrCode->status == QRStatusEnum::INSTOCK){
                //Kiểm tra sản phẩm trả cho khách có trong showroom ko
                if(!empty($request->showroom_id)){
                    if($productQrCode->warehouse_type != ShowroomWarehouse::class || $productQrCode->warehouse->showroom_id != $request->showroom_id)
                    {
                        return response()->json([
                            'success' => 0,
                            'message' => 'Mã QR sản phẩm trả không có trong Showroom',
                        ], 200);
                    }
                }
            }

            $productQrCode->loadMissing([
                'reference:id,name,price,sale_price,production_time,sku,images',
                'timeCreateQR:id,quantity_product,variation_attributes,times_export',
                'warehouse.showroom',
                'reference.parentProduct'
            ]);

            return response()->json([
                'success' => 1,
                'message' => 'Quét thành công',
                'data' => $productQrCode,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' =>  $e->getMessage(),
            ], 400);
        }
    }

    public function submitCreate(ExchangeGoodsRequest $request)
    {
        $requestData = $request->input();
        DB::beginTransaction();
        try{
            $totalQuantity = 0;
            $totalAmount = 0;
            $arrInfo = [];//Mảng lưu thông tin của sp đổi/trả sau khi đổi trạng thái
            // Sử dụng array_reduce để tính toán số lần xuất hiện của mỗi key
            $counts = array_reduce($requestData['list_qrcode'], function($carry, $item) {
                foreach ($item as $key => $value) {
                    $carry[$key] = isset($carry[$key]) ? $carry[$key] + 1 : 1;
                }
                return $carry;
            }, []);
            //Kiểm tra độ dài của mỗi list đổi/trả, nếu < 1 thì hiện thông báo lỗi
            if($counts['pay'] < 1 || $counts['exchange'] < 1)
            {
                return $this
                ->httpResponse()
                ->setError()
                ->setMessage(__('Số lượng sản phẩm đổi hoặc trả phải ít nhất là 1'));
            }

            foreach ($requestData['list_qrcode'] as $key => $value) {
                # code...
                if(array_key_exists('pay', $value) && array_key_exists('exchange', $value)){
                    if(!empty($value['exchange']) && !empty($value['pay'])){

                        $id_exchange = $value['exchange']['id'];
                        $id_pay = $value['pay']['id'];

                        //Tìm kiếm đơn hàng từ id qrcode của sản phẩm đổi từ khách hàng
                        $showroomOrder = ShowroomOrder::query()
                        ->where('list_id_product_qrcode', 'like', '%[' .$id_exchange. ',%')
                        ->orWhere('list_id_product_qrcode', 'like', '%,' .$id_exchange. ',%')
                        ->orWhere('list_id_product_qrcode', 'like', '%,' .$id_exchange. ']%')
                        ->orWhere('list_id_product_qrcode', 'like', '%[' .$id_exchange. ']%')
                        ->first();

                        //Kiểm tra sản phẩm của khách có tồn tại đơn hàng nào không
                        if(empty($showroomOrder))
                        {
                            DB::rollBack();

                            return $this
                            ->httpResponse()
                            ->setError()
                            ->setMessage('Sản phẩm ' . $value['exchange']['reference']['name'] . ', SKU: '. $value['exchange']['reference']['sku'] . ' không tồn tại đơn hàng nào!!');
                        }

                        //Kiểm tra đơn hàng tìm được có đúng loại showroom
                        if($showroomOrder->where_type == Showroom::class)
                        {
                            $list_qr = json_decode($showroomOrder->list_id_product_qrcode, true);

                            if ($list_qr !== null) {
                                // Mảng đã được chuyển đổi thành công
                                // Bây giờ bạn có thể sử dụng mảng $list_qr trong mã của mình
                                $key = array_search($id_exchange, $list_qr);
                                if ($key !== false) {
                                    $list_qr[$key] = $id_pay;
                                    $jsonStringUpdated = json_encode($list_qr);

                                    //Cập nhật danh sách sp mới cho đơn hàng
                                    $showroomOrder->update([
                                        'list_id_product_qrcode' => $jsonStringUpdated
                                    ]);
                                    //Cập nhật trạng thái của qrcode
                                    $qrCodeExchange = ProductQrcode::query()->where('id', $id_exchange)->first();
                                    $qrCodePay = ProductQrcode::query()->where('id', $id_pay)->first();

                                    if(!empty($qrCodeExchange) && !empty($qrCodePay))
                                    {
                                        //Cập nhật trạng thái cho QR sản phẩm đổi từ khách hàng
                                        $qrCodeExchange->update([
                                            'status' => QRStatusEnum::INSTOCK,
                                            'warehouse_id' => $requestData['showroom_id'],
                                            'warehouse_type' => ShowroomWarehouse::class,
                                            'has_exchange' => 1
                                        ]);

                                        //Cập nhật trạng thái QR sản phẩm trả từ showroom
                                        $qrCodePay->update([
                                            'status' => QRStatusEnum::SOLD,
                                            'warehouse_id' => $requestData['showroom_id'],
                                            'warehouse_type' => ShowroomWarehouse::class,
                                            'has_exchange' => 1
                                        ]);
                                        //Cập nhật số lượng cho Showroom
                                        //Trừ sp trả
                                        $showroomProductPay = ShowroomProduct::query()->where([
                                            'where_type' => ShowroomWarehouse::class,
                                            'where_id' => $requestData['showroom_id'],
                                            'product_id' => $qrCodePay->reference_id
                                        ])->first();

                                        if($showroomProductPay->quantity_qrcode > 0){
                                            //Trừ số lượng
                                            $showroomProductPay?->update([
                                                'quantity_qrcode' =>  $showroomProductPay->quantity_qrcode - 1
                                            ]);
                                        }

                                        //Cộng sp đổi
                                        $showroomProductExchange = ShowroomProduct::query()->where([
                                            'where_type' => ShowroomWarehouse::class,
                                            'where_id' => $requestData['showroom_id'],
                                            'product_id' => $qrCodeExchange->reference_id
                                        ])->first();

                                        if(!$showroomProductExchange){
                                            ShowroomProduct::query()->create([
                                                'warehouse_id' =>$requestData['showroom_id'],
                                                'product_id' => $qrCodeExchange->reference_id,
                                                'quantity_qrcode' => 1,
                                                'where_id' => $requestData['showroom_id'],
                                                'where_type' => ShowroomWarehouse::class,
                                            ]);
                                        }else{
                                            $showroomProductExchange->update([
                                                'quantity_qrcode' =>  $showroomProductExchange->quantity_qrcode + 1
                                            ]);

                                        }


                                        //Cập nhật lại số lượng
                                        //Kiểm tra sp trả là lẻ hay lấy từ lô
                                        $batchDetail = ProductBatchDetail::query()->where(['qrcode_id' => $id_pay])->first();
                                        if(!empty($batchDetail))//Sp là từ lô
                                        {
                                            $batch = $batchDetail->productBatch;
                                            if($batch->quantity > 0){
                                                //Trừ số lượng
                                                $batch->update([
                                                    'quantity' => $batch->quantity - 1
                                                ]);
                                                //Xoá item trong chi tiết lô
                                                $batchDetail->delete();
                                            }
                                        }
                                        //
                                        array_push($arrInfo, [
                                            'order_id' => $showroomOrder->id,
                                            'pay' => [
                                                'id' => $id_pay,
                                                'price' => $value['pay']['reference']['price'],
                                            ],
                                            'exchange' => [
                                                'id' => $id_exchange,
                                                'price' => $value['exchange']['reference']['price'],
                                            ],
                                        ]);
                                    }

                                    $totalQuantity++;
                                    $totalAmount += $value['exchange']['reference']['price'];
                                }
                            } else {
                                // Có lỗi xảy ra khi chuyển đổi chuỗi JSON
                                DB::rollBack();

                                return $this
                                ->httpResponse()
                                ->setError()
                                ->setMessage('Có lỗi xảy ra. Vui lòng thử lại!!');
                            }

                        }else{
                            DB::rollBack();

                            return $this
                            ->httpResponse()
                            ->setError()
                            ->setMessage('Sản phẩm ' . $value['exchange']['reference']['name'] . ', SKU: '. $value['exchange']['reference']['sku'] . 'không thuộc showroom hiện tại!!');
                        }
                    }
                }
            }
            //Tạo phiếu đổi trả hàng
            $exchangeGoods = ExchangeGoods::query()->create([
                'showroom_id' => $requestData['showroom_id'],
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
                'status' => ExchangeGoodsStatusEnum::WAITING,
                'description' => $requestData['description']
            ]);

            if(!empty($exchangeGoods)){
                for ($i=0; $i < $totalQuantity; $i++) {
                    //Tạo chi tiết đơn cho phiếu đổi trả hàng
                    ExchangeGoodsDetail::query()->create([
                        'parent_id' => $exchangeGoods->id,
                        'order_id' => $arrInfo[$i]['order_id'],
                        'qr_id_change' => $arrInfo[$i]['exchange']['id'],
                        'price_product_change' => $arrInfo[$i]['exchange']['price'],
                        'qr_id_pay' => $arrInfo[$i]['pay']['id'],
                        'price_product_pay' => $arrInfo[$i]['pay']['price'],
                    ]);
                }
            }

            //Tạo noti thông báo
            $arrNoti = [
                'action' => ' tạo ',
                'permission' => "exchange-goods.*",
                'route' => route('exchange-goods.view', $exchangeGoods),
                'status' => 'Đã tạo đơn'
            ];
            send_notify_cms_and_tele($exchangeGoods, $arrNoti);
            DB::commit();

            return response()->json([
                'success' => 1,
                'message' => 'Đổi trả hàng thành công!!',
                'data' => $exchangeGoods,
            ], 200);
        }catch(Exception $err){
            DB::rollBack();

            return $this
            ->httpResponse()
            ->setError()
            ->setMessage($err->getMessage());
        }
    }

    public function view(ExchangeGoods $exchange)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/showroom/js/exchange-goods.js',
            'vendor/core/plugins/showroom/js/scanner.js',
        ])
        ->addStylesDirectly([
            'vendor/core/plugins/showroom/css/exchange-goods.css'
        ])
        ->addScripts(['input-mask','sortable']);

        Assets::usingVueJS();

        $this->pageTitle(__('Thông tin phiếu đổi trả hàng'));

        $list_pay = [];
        $list_exchange = [];

        foreach ($exchange->exchangeDetail as $key => $value) {
            $productPay = ProductQrcode::query()->where('id', $value->qr_id_pay)->first();
            $productExchange = ProductQrcode::query()->where('id', $value->qr_id_change)->first();

            if(!empty($productPay))
            {
                $productPay->loadMissing([
                    'reference:id,name,price,sale_price,production_time,sku,images',
                    'timeCreateQR:id,quantity_product,variation_attributes,times_export',
                    'warehouse.showroom',
                    'reference.parentProduct'
                ]);

                array_push($list_pay, $productPay);
            }

            if(!empty($productExchange))
            {
                $productExchange->loadMissing([
                    'reference:id,name,price,sale_price,production_time,sku,images',
                    'timeCreateQR:id,quantity_product,variation_attributes,times_export',
                    'warehouse.showroom',
                    'reference.parentProduct'
                ]);

                array_push($list_exchange, $productExchange);
            }
        }

        $showroom = $exchange->showroom;

        return view('plugins/showroom::exchange-goods.view', compact('list_pay', 'list_exchange', 'showroom'));
    }
}
