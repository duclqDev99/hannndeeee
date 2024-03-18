<?php

use Botble\OrderTransaction\Events\OrderTransactionEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::group([
    'prefix' => 'api/v1',
    'namespace' => 'Botble\OrderTransaction\Http\Controllers\Api',
], function () {

        Route::group(['prefix' => 'order-transactions'], function(){
            Route::post('/create-order-transaction',[
                'uses' => 'OrderTransactionApiController@createOrderTransaction'
            ]);
            Route::post('/confirm-transaction',[
                'as' => 'confirm',
                'uses' => 'OrderTransactionApiController@confirmTransaction'
            ]);

        });
        Route::group(['prefix' => 'order-transactions-client', 'middleware' => ['web','auth'] ], function () {
                // Route::post('/transaction-code');
                Route::post('/send-request-payment/{order}',[
                    'uses' => 'OrderTransactionApiController@sendRequestPayment',
                    'as' => 'order-transactions.send-request-payment',
                    'permission' => false,
                    // 'permission' => 'showroom.orders.create'
                ]);
                Route::get('/notifications/{order_code}', function ($orderid) {
                    $response = new Symfony\Component\HttpFoundation\StreamedResponse(function() use($orderid){
                        $startTime = time();
                        while (true) {
                            if ((time() - $startTime) > 5) {
                                break;
                            }
                            $orderTransaction= '';
                            Log::info('notifications : '. $orderid . " sse");
                            if (Cache::has("orderTransactionConfirm-$orderid")) {
                                $orderTransaction = Cache::get("orderTransactionConfirm-$orderid");
                                if($orderTransaction == 1){
                                    $data = [
                                        'error_code' => 0,
                                        'msg' => 'ccc',
                                        'data' => $orderTransaction,
                                    ];
                                    Cache::forget("orderTransactionConfirm-$orderid");
                                    echo 'data: ' . json_encode($data) . "\n\n";
                                    ob_flush();
                                    flush();
                                    break;
                                }
                                
                            }
                            if (connection_aborted()) { // ngắt vòng lặp nếu kết nối bị ngắt
                                break;
                            }
                            sleep(1);
                        }
                    });
                
                    $response->headers->set('Content-Type', 'text/event-stream');
                    $response->headers->set('Cache-Control', 'no-cache');
                    $response->headers->set('Connection', 'keep-alive');
                
                    return $response;
                });
        });

});
