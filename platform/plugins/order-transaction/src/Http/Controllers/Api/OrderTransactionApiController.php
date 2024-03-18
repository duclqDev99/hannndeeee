<?php

namespace Botble\OrderTransaction\Http\Controllers\Api;


use Illuminate\Support\Facades\Cache;





use Botble\Base\Facades\BaseHelper;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;

use Botble\Ecommerce\Models\OrderHistory;
use Botble\OrderTransaction\Services\RefundPointService;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderTransactionApiController extends BaseController
{
    protected $refundPointService;

    public function __construct(RefundPointService $refundPointService)
    {
        $this->refundPointService = $refundPointService;
    }
    /**
     * Create Order Transaction
     *
     * @group Order Showroom
     * @bodyParam order_id string required 'ID đơn hàng'  Example: 107
     * @bodyParam order_code string required 'Mã đơn hàng'  Example: #10000107
     * @bodyParam amount int required 'Tổng số tiền'  Example: 1000000
     * @bodyParam content_banking string required 'Nội dung chuyển khoản'  Example: Chuyển khoản đơn hàng #10000107
     * @response {
     * "error_code": 0,
     * "data": {
     *          "charge_id": "B5GHOZWVVG",
     * },
     * "msg": "Tạo giao dịch thanh toán thành công"
     * }
     */
    public function createOrderTransaction(Request $request)
    {
        \Log::info('57createOrderTransaction');

        $order_id = $request->order_id;
        $order_code = $request->order_code;
        $amount = $request->amount;
        $contentBanking = $request->content_banking;

        $order = Order::query()->where([
            'id' => $order_id,
            'code' => $order_code,
        ])->first();
        \Log::info('createOrderTransaction', ['order' => $order->toArray()]);

        if ($order && $order->amount == $amount) {
            if ($order->payment()->exists()) {
                $order->payment->update(['content_banking' => $contentBanking]);
                $orderCode = $order->code;
                $money = format_price($order->amount);
                OrderHistory::query()->create([
                    'action' => 'confirm_payment',
                    'description' => "Đã khởi tạo giao dịch chuyển khoản đơn hàng $orderCode với số tiền: $money",
                    'order_id' => $order->id,
                ]);
                $dataRes = [
                    'error_code' => 0,
                    'msg' => 'Tạo giao dịch thanh toán thành công',
                    'data' => [
                        'transaction_id' => $order->payment->id,
                        'app_url' => env('APP_URL'),
                    ],
                ];
                \Log::info('87createOrderTransaction', ['response' => $dataRes]);
                return response()->json($dataRes, 200);
            } else {

                $dataRes = [
                    'error_code' => 1,
                    'msg' => 'Khởi tạo đơn hàng thất bại!!',
                    'data' => [],
                ];
                \Log::info('96createOrderTransaction', ['response' => $dataRes]);

                return response()->json($dataRes, 200);
            }
        } else {
            $dataRes = [
                'error_code' => 1,
                'msg' => 'Không tìm thấy đơn hàng',
                'data' => [],
            ];
            \Log::info('106createOrderTransaction', ['response' => $dataRes]);

            return response()->json($dataRes, 200);
        }
    }

    /**
     * Confirm Transaction
     *
     * @group Order Showroom
     * @bodyParam charge_id string required 'Mã giao dịch'  Example: B5GHOZWVVG
     * @response {
     * "error_code": 0,
     * "data":[],
     * "msg": "Cập nhật thanh toán thành công"
     * }
     */
    public function confirmTransaction(Request $request)
    {
        $paymentId = $request->get('transaction_id');
        $appURL = $request->get('app_url');
        $code = $request->get('code');
        Log::info('120confirmTransaction', [ 'response' => $request->all()]);
        
        $payment = Payment::query()->find($paymentId);
    
        if($payment){
            $order = Order::query()->find($payment->order_id);
            if ($order) {
                if ($order->payment()->exists()) {
                    if($order->payment->status != PaymentStatusEnum::COMPLETED){
                        $orderid = $order->id;
                        Cache::put("orderTransactionConfirm-$orderid", 1, 60);
                        $order->payment->update([
                            'status' => PaymentStatusEnum::COMPLETED,
                            'content_banking' => $code ?? '',
                        ]);

                        // Nếu ko phải đơn từ trang chủ thì hoàn thành đơn
                        // Nếu đơn từ trang chủ phải chờ giao thành thành công
                        if(!$order->is_from_home){
                            $order->update([
                                'status' => OrderStatusEnum::COMPLETED,
                                'completed_at' => now(),
                            ]);
                        }

                        // cập nhật QR đã bán
                        $idProductQrcodeList = ShowroomOrder::query()
                            ->where('order_id', $order->id)
                            ->select('list_id_product_qrcode')
                            ->first();
                            if($idProductQrcodeList){
                                $idProductQrcodeList = $idProductQrcodeList->list_id_product_qrcode;
                                    ProductQrcode::query()->whereIn('id', $idProductQrcodeList)->update(['status' => QRStatusEnum::SOLD]);
                            }
                        $money = format_price($order->amount);
                        OrderHistory::query()->create([
                            'action' => 'confirm_payment',
                            'description' => "Thanh toán đã được xác nhận với số tiền: $money",
                            'order_id' => $order->id,
                        ]);
                        if($order->has_refund_point){
                            $this->refundPointService->refundPoint($paymentId,$order);
                        }
                    }

                    $dataRes = [
                        'error_code' => 0,
                        'msg' => 'Cập nhật thanh toán thành công',
                        'data' => [],
                    ];
                    return response()->json($dataRes, 200);
                } else {
                    $dataRes = [
                        'error_code' => 1,
                        'msg' => 'Đơn hàng chưa tạo giao dịch thanh toán',
                        'data' => [],
                    ];
                    return response()->json($dataRes, 200);
                }
            }
        }
        $dataRes = [
            'error_code' => 1,
            'msg' => 'Không tìm thấy giao dịch',
            'data' => [],
        ];
        return response()->json($dataRes, 200);

        $dataRes = [
            'error_code' => 1,
            'msg' => 'Wrong APP URL',
            'data' => [],
        ];
        return response()->json($dataRes, 200);

    }

    public function sendRequestPayment(Order $order, Request $request){
        $hostProd = 'https://handee.wghn.net';
        if(str_contains($request->fullUrl(), $hostProd)){
            $showroomOrder = ShowroomOrder::query()->where('order_id', $order->id)->first();
            $vid = '';
            if($order->user_id){
                $user = Customer::find($order->user_id);
                $vid = $user->vid;
            }
            if($showroomOrder)
            {
                $showroom = $showroomOrder->where;
                
                if($showroom)
                {
                    $data =[
                        "data_provider" => [
                            "order_id" => $order->id,
                            "order_code" => $order->code,
                            'amount' => $order->amount,
                            'showroom_id' => $showroom->id,
                        ],
                        "provider" => $showroom->provider_banking,
                        "type" => "bank",
                        "money" => intval($order->amount),
                    ];
                    $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
                    $response = $client->post("https://api-payment-prod.vcallid.com/api/v1/paymenthub/create_request_handee?uid=$vid&is_vCall=1", [
                        'body' => json_encode($data)
                    ]);
                    \Log::info('306sendRequestPayment', ['data' => "https://api-payment-prod.vcallid.com/api/v1/paymenthub/create_request_handee?uid=$vid&is_vCall=1"]);
                    \Log::info('307sendRequestPayment', ['data' => $data]);
                    \Log::info('308sendRequestPayment', ['order' => $order->toArray(), 'response' => (string) $response->getBody()]);
                    $responseData = json_decode($response->getBody(), true);
                        if($responseData['error_code'] == 0){
                            $dataRes = [
                                'error_code' => 0,
                                'msg' => 'Thành công',
                                'data' => [
                                    'amount' =>$order->amount,
                                    'qr_image' => $responseData['data']['bank']['qr_image'],
                                ],
                            ];
                            return response()->json($dataRes, 200);
                        }
                        $dataRes = [
                            'error_code' => 1,
                            'msg' => 'Lỗi',
                            'data' => $responseData,
                        ];
                        return response()->json($dataRes, 200);
                }
            }
        }
        else{
            $dataRes = [
                'error_code' => 1,
                'msg' => 'Không tạo được yêu cầu thanh toán',
                'data' => [],
            ];
            return response()->json($dataRes, 200);
        }

    }
}
