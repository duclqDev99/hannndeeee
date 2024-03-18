<?php

namespace Botble\ViettelPost\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentHistoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentInterface;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\ViettelPost\ViettelPost;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Throwable;

class ViettelPostController extends BaseController
{
    protected string|int|null $userId = 0;

    public function __construct(
        protected ShipmentInterface $shipmentRepository,
        protected ShipmentHistoryInterface $shipmentHistoryRepository,
        protected ViettelPost $viettelPost
    ) {
        if (is_in_admin(true) && Auth::check()) {
            $this->userId = Auth::id();
        }
    }

    public function show(int $id, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);
        $this->check($shipment);

        $order = $shipment->order;

        $content = '';
        $errors = [];

        try {
            $shipmentViettelPost = $this->viettelPost->retrieveShipment($shipment->shipment_id);

            if ($shipmentViettelPost && Arr::get($shipmentViettelPost, 'status') == 'SUCCESS') {
                $rate = [];
                $payment = $order->payment;
                if ($payment->payment_channel->getValue() == PaymentMethodEnum::COD) {
                    $extra = Arr::get($shipmentViettelPost, 'extra', []);
                    $codAmount = Arr::get($extra, 'COD.amount');
                    if ($codAmount && $order->amount != $codAmount) {
                        Arr::set($shipmentViettelPost, 'extra.COD.amount', $order->amount);

                        $shipmentViettelPost = $this->refreshShipment($shipmentViettelPost, $order);
                        $rates = Arr::get($shipmentViettelPost, 'rates', []);
                        $rate = Arr::first($rates, function ($value) use ($order) {
                            return Arr::get($value, 'servicelevel.token') == $order->shipping_option;
                        });

                        if ($rate) {
                            $shipment->shipment_id = Arr::get($shipmentViettelPost, 'object_id');
                            $shipment->rate_id = Arr::get($rate, 'object_id');
                            $shipment->save();
                        }
                    }
                }

                if (! $rate) {
                    $rates = Arr::get($shipmentViettelPost, 'rates', []);
                    $rate = Arr::first($rates, function ($value) use ($shipment) {
                        return Arr::get($value, 'object_id') == $shipment->rate_id;
                    });
                }

                $content = view('plugins/viettel-post::info', compact('rate', 'shipmentViettelPost', 'shipment', 'order'))->render();
            } else {
                if (Arr::has($shipmentViettelPost, 'message')) {
                    $errors[] = Arr::get($shipmentViettelPost, 'message');
                } else {
                    $errors[] = trans('plugins/viettel-post::viettelPost.shipment_object_id_not_found', ['id' => $shipment->shipment_id]);
                }
            }
        } catch (Throwable $th) {
            $errors[] = $th->getMessage();
        }

        return $response->setError((bool) $errors)
            ->setData([
                'html' => $content,
                'errors' => $errors,
            ])
            ->setMessage($errors ? Arr::first($errors) : '');
    }

    public function createTransaction(int $id, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $this->check($shipment);

        if (! $this->viettelPost->canCreateTransaction($shipment)) {
            abort(404);
        }

        $message = trans('plugins/viettel-post::viettel-post.transaction.created_success');

        $errors = [];
        $responseData = [];

        try {
            $transaction = $this->viettelPost->createTransaction($shipment->rate_id);
            if (Arr::get($transaction, 'status') == 'SUCCESS') {
                $shipment->tracking_link = Arr::get($transaction, 'tracking_url_provider');
                $shipment->label_url = Arr::get($transaction, 'label_url');
                $shipment->tracking_id = Arr::get($transaction, 'object_id');
                $shipment->metadata = json_encode($transaction);
                $shipment->status = ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT;
                $shipment->save();

                $this->shipmentHistoryRepository->createOrUpdate([
                    'action' => 'create_transaction',
                    'description' => trans('plugins/viettel-post::viettel-post.transaction.created', [
                        'tracking' => Arr::get($transaction, 'tracking_number'),
                    ]),
                    'order_id' => $shipment->order_id,
                    'user_id' => $this->userId,
                    'shipment_id' => $shipment->id,
                ]);

                $this->shipmentHistoryRepository->createOrUpdate([
                    'action' => 'update_status',
                    'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                        'status' => ShippingStatusEnum::getLabel(ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT),
                    ]),
                    'order_id' => $shipment->order_id,
                    'user_id' => $this->userId,
                    'shipment_id' => $shipment->id,
                ]);
            } else {
                if ($errors = Arr::get($transaction, 'messages', [])) {
                    $message = collect($errors)->pluck('text')->implode('; ');
                }
            }
        } catch (Exception $ex) {
            $errors[] = $ex->getMessage();
            $message = $ex->getMessage();
        }

        $responseData['errors'] = (array) $errors;

        return $response->setError((bool) count($errors))
            ->setMessage($message)
            ->setData($responseData);
    }

    protected function refreshShipment(array $shipmentViettelPost, Order $order)
    {
        if (! Arr::has($shipmentViettelPost, 'extra.reference_2')) {
            Arr::set($shipmentViettelPost, 'extra.reference_2', $order->code);
        }

        $params = [
            'address_from' => Arr::get($shipmentViettelPost, 'address_from.object_id'),
            'address_to' => Arr::get($shipmentViettelPost, 'address_to.object_id'),
            'extra' => Arr::get($shipmentViettelPost, 'extra'),
            'parcels' => [Arr::get($shipmentViettelPost, 'parcels.0.object_id')],
        ];

        if (Arr::has($shipmentViettelPost, 'customs_declaration')) {
            $params['customs_declaration'] = Arr::get($shipmentViettelPost, 'customs_declaration');
        }

        if (Arr::has($shipmentViettelPost, 'metadata')) {
            $params['metadata'] = Arr::get($shipmentViettelPost, 'metadata');
        }

        return $this->viettelPost->createShipment($params);
    }

    public function getRates(Request $request, BaseHttpResponse $response)
    {
        return response()->json(['success' => true],200);
        // $shipment = Shipment::query()->findOrFail($id);

        // $this->check($shipment);

        // $content = '';
        // $errors = [];
        // $order = $shipment->order;

        // try {
        //     $shipmentViettelPost = $this->viettelPost->retrieveShipment($shipment->shipment_id);

        //     $shipmentViettelPost = $this->refreshShipment($shipmentViettelPost, $order);
        //     $rates = Arr::get($shipmentViettelPost, 'rates', []);

        //     $rates = $this->viettelPost->sortRates($rates);

        //     $rate = Arr::first($rates, function ($value) use ($order) {
        //         return Arr::get($value, 'servicelevel.token') == $order->shipping_option;
        //     });

        //     if ($rate) {
        //         $rates = Arr::where($rates, function ($value) use ($rate) {
        //             return Arr::get($value, 'servicelevel.token') !== Arr::get($rate, 'servicelevel.token');
        //         });
        //     }

        //     $content = view('plugins/viettel-post::rates', compact('rates', 'shipmentViettelPost', 'shipment', 'order', 'rate'))->render();
        // } catch (Throwable $th) {
        //     $errors[] = $th->getMessage();
        // }

        // return $response->setError((bool) $errors)
        //     ->setData([
        //         'html' => $content,
        //         'errors' => $errors,
        //     ])
        //     ->setMessage($errors ? Arr::first($errors) : '');
    }

    public function updateRate(int $id, Request $request, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $this->check($shipment);

        $order = $shipment->order;

        $content = '';
        $errors = [];

        try {
            $shipmentViettelPost = $this->viettelPost->retrieveShipment($shipment->shipment_id);

            $shipmentViettelPost = $this->refreshShipment($shipmentViettelPost, $order);

            $rates = Arr::get($shipmentViettelPost, 'rates', []);
            $rates = $this->viettelPost->sortRates($rates);

            $rate = Arr::first($rates, function ($value) use ($order) {
                return Arr::get($value, 'servicelevel.token') == $order->shipping_option;
            });

            if (! $rate) {
                $rate = Arr::first($rates, function ($value) use ($request) {
                    return Arr::get($value, 'servicelevel.token') == $request->input('shipping_option');
                });
            }

            if ($rate) {
                $order->shipping_option = Arr::get($rate, 'servicelevel.token');
                $order->save();
                $shipment->shipment_id = Arr::get($shipmentViettelPost, 'object_id');
                $shipment->rate_id = Arr::get($rate, 'object_id');
                $shipment->save();

                $content = view('plugins/viettel-post::info', compact('rate', 'shipmentViettelPost', 'shipment', 'order'))->render();
            } else {
                $errors[] = 'Rate not found';
            }
        } catch (Throwable $th) {
            $errors[] = $th->getMessage();
        }

        return $response->setError((bool) $errors)
            ->setData([
                'html' => $content,
                'errors' => $errors,
            ])
            ->setMessage($errors ? Arr::first($errors) : trans('plugins/viettel-post::viettel-post.updated_rate_success'));
    }

    protected function check(Shipment $shipment): bool
    {
        $order = $shipment->order;

        if (! is_in_admin(true) && is_plugin_active('marketplace')) {
            $vendor = auth('customer')->user();
            $store = $vendor->store;

            if ($store->id != $order->store_id) {
                abort(403);
            }
        }

        if (! $order
            || ! $order->id
            || $order->shipping_method->getValue() != VIETTEL_POST_SHIPPING_METHOD_NAME
            || ! $shipment->shipment_id) {
            abort(404);
        }

        return true;
    }

    public function viewLog(string $logFile)
    {
        $logPath = storage_path('logs/' . $logFile);

        if (! File::exists($logPath)) {
            abort(404);
        }

        return nl2br(File::get(storage_path('logs/' . $logFile)));
    }
    public function ajaxGetAddressAllShowroomViettelPost(Request $request)
    {
        $token = $this->getTokenVietelPost();
        $url = 'https://partner.viettelpost.vn/v2/user/listInventory';
        $client = new Client(['headers' => ['Content-Type' => 'application/json', 'Token' => $token]]);
        $response = $client->get($url);
        $resData = json_decode((string)$response->getBody(), true);
        if($resData['status'] == 200 && $resData['error'] == false){
            return response()->json($resData['data']);
        }
    }
}
