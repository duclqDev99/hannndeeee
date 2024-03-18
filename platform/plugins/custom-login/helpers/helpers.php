<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

if (!function_exists('getApiPkey')) {
    function getApiPkey($name)
    {
        $rs = config('pkey');
        if(!$rs){
            $rs = openssl_pkey_get_private('file://'.base_path().'/private_key_web.pem');
            Config::set('pkey', $rs); 
        }
        return $rs;
    }
}
if (!function_exists('getSignature')) {
    function getSignature($data = ["user_id"=> 70295, "page" => 1, "limit" => 10, "category_id" => null, "post_type" => 1])
    {
        $dataNew = [];
        ksort($data);

        foreach ($data as $key => $value) {
            if(gettype($value) == 'array') $dataNew[$key] = json_encode($value, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            else if(gettype($value) == 'object') $dataNew[$key] = json_encode($value, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            else  $dataNew[$key] = $value;
        }

        $plaintext = implode("|", $dataNew);
        $privateKey = getApiPkey('pkey');
        $binary_signature = "";
        $algo = "SHA256";

        openssl_sign($plaintext, $binary_signature, $privateKey, $algo);
        
        $data['signature'] = base64_encode($binary_signature);
        // dump('data input::',$data, 'data đã qua json_encode::', $dataNew,'plaintext implode từ data đã qua xử lý::', $plaintext, 'signature::', @$data['signature']);
        return json_encode($data);
    }
}
if (!function_exists('createClient')) {
    function createClient($baseUri = null)
    {
        $maxRetries = 3;

        $decider = function(int $retries, RequestInterface $request, ResponseInterface $response = null) use ($maxRetries) : bool {
            $rs = $retries < $maxRetries
                    && null !== $response 
                    // && 500 <= $response->getStatusCode();
                    && (503 == $response->getStatusCode() || 504 == $response->getStatusCode());

            if($rs) Log::channel('retry_api')->error(date("Y-m-d h:i:sa"). " " . $response->getStatusCode(). " " . $request->getUri()->getPath());

            return $rs;
        };

        $delay = function() : int {
            return 3000;
        };

        $stack = HandlerStack::create();
        $stack->push(Middleware::retry($decider, $delay));

        if($baseUri === null) $baseUri =  env('WGHN_API_PREFIX', 'https://api-vnews-dev2.vcallid.com/api/v1/vnews/');
        return new Client(['base_uri' => $baseUri, 
                            'handler'  => $stack, 
                            'headers' => ['Content-Type' => 'text/plain', 'Accept' => 'application/json','connect_timeout' => 2, 'timeout' => 2,], 
                            'debug' => false
                        ]);
    }
}