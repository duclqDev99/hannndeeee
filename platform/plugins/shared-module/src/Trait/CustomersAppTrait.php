<?php
namespace Botble\SharedModule\Trait;
use GuzzleHttp\Client;

trait CustomersAppTrait {
    public function getInfoCustomerApi($phone){
        $data = [
            "phone" => $phone
        ];
        $client = new Client(['headers' => [
            'Content-Type' => 'application/json',
            'Authen' => env('GET_ACCOUNT_VID_API_TOKEN')
        ]]);
        $response = $client->post('https://api-other-services.vcallid.com/api/v1/for_cms/mapping_account_vid', [
            'body' => json_encode($data)
        ]);
        $responseData = json_decode($response->getBody(), true);
        return $responseData;
    }
}