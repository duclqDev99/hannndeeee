<?php

namespace Botble\OrderTransaction\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\Payment\Models\Payment;
use Botble\Showroom\Models\ShowroomOrder;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RefundPointService
{
    public function refundPoint($paymentId,$order){
        $customer = Customer::query()->find($order->user_id);
        Log::info('RefundPoint '. $paymentId);
        
        if($customer && $customer->vid && env('REFUND_POINT_APP_URL') !== null){
            $orderCode = $order->code;
            $showroomOrder = ShowroomOrder::query()->where('order_id', $order->id)->first();
        Log::info($showroomOrder);

            if($showroomOrder)
            {
                Log::info('showroomOrder RefundPoint');

                $showroom = $showroomOrder->where;
                $serviceId = -1;
                if($showroom)
                {
                Log::info('showroom');

                    switch($showroom->provider_banking){
                        case 'handee_hn':
                            $serviceId = 25;
                            break;
                        case 'handee_hcm':
                            $serviceId = 26;
                            break;
                        case 'handee_dn':
                            $serviceId = 27;
                            break;
                    }
                    Log::info($serviceId);

                    if($serviceId != -1){
                        $body_data = [
                            'user_id' => $customer->vid,
                            'total_payment' => intval($order->amount),
                            'service_id' => $serviceId,
                            'pay_id' => 1, // ví điểm thưởng
                            'transaction_id' => $paymentId,
                            'message' => "Hoàn point vào ví từ Handee Retail, đơn hàng $orderCode"
                        ];
                        Log::info($body_data);

                        $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
                        $response = $client->post(env("REFUND_POINT_APP_URL"), [
                            'body' => json_encode($body_data)
                        ]);
                        $responseData = json_decode($response->getBody(), true);
                        Log::info($responseData);
                        if($responseData['error_code'] == 0){
                            $refundedPointAmount = $responseData['data']['total_point_bonus'];
                            $payment = Payment::find($paymentId);
                            if ($payment) {
                                $payment->update([
                                    'is_refunded_point' => 1,
                                    'refunded_point_amount' => $refundedPointAmount,
                                ]);
                                Log::info("Đã hoàn tiền cho payment $paymentId với số điểm: $refundedPointAmount");
                            }
                        }
                    }
                    
                }
            }
 
        }

        
    }
}