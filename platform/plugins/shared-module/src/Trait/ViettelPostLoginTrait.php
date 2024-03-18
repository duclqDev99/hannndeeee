<?php
namespace Botble\SharedModule\Trait;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

trait ViettelPostLoginTrait{
    public function getTokenVietelPost(){
        if (Cache::has("shipping_viettel_post_token")) {
            return Cache::get("shipping_viettel_post_token");
        }
        else{
            $urlLogin = 'https://partner.viettelpost.vn/v2/user/Login';
            $dataLogin = [
                'USERNAME' => setting('shipping_viettel_post_username'),
                'PASSWORD' => setting('shipping_viettel_post_password'),
            ];
            $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
            $response = $client->post($urlLogin, [
                'body' => json_encode($dataLogin)
            ]);
            $resData = json_decode((string)$response->getBody(), true);
            if($resData['status'] == 200 && $resData['error'] == false){
                $token = $resData['data']['token'];
                $expirationMilliseconds = $resData['data']['expired'];

                // Chuyển đổi mili giây sang giây
                $expirationSeconds = $expirationMilliseconds / 1000;
                
                // Tạo đối tượng Carbon từ thời gian hết hạn
                $expirationTime = Carbon::createFromTimestamp($expirationSeconds);
                
                // Tính thời gian còn lại cho đến thời điểm hết hạn
                $now = Carbon::now();
                $secondsUntilExpiration = $expirationTime->diffInSeconds($now) - 120;

                // Lưu token vào cache với thời gian hết hạn tương ứng
                Cache::put("shipping_viettel_post_token", $token,$secondsUntilExpiration);
                return $token;

            }
        }
    }
}