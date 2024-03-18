<?php
namespace Botble\SharedModule\Trait;

use GuzzleHttp\Psr7\Request as GuzzleRequest;

trait LoginWithAppTrait {
    function sendOTPTrait($phoneSendToApp, $isSms = 0)
    {

        $client = createClient();
        $body = getSignature([
            'phone' => $phoneSendToApp,
            'is_send_sms' => $isSms,
        ]);
        $headers = [
            'Content-Type' => 'application/json'
        ];
        $request = new GuzzleRequest('POST', 'get_otp_login_web', $headers, $body);
        $res = $client->sendAsync($request)->wait();
        return $res;
    }
    function loginWithOTP($data, $isSms = 0)
    {
        $client = createClient();
        $data['is_send_sms'] = $isSms;
        $body = getSignature($data);
        $headers = [
            'Content-Type' => 'application/json'
        ];
        $request = new GuzzleRequest('POST', 'login_web', $headers, $body);
        $res = $client->sendAsync($request)->wait();

        return json_decode($res->getBody());

        // xử lý nếu data -> user_info == null thì cho dẫn đến route register user
    }
}