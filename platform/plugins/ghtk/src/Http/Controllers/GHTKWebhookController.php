<?php

namespace Botble\GHTK\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\GHTKStatusEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentHistoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\ShipmentInterface;
use Botble\GHTK\GHTK;
use Botble\Payment\Enums\ExtendedPaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Shippo\Shippo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GHTKWebhookController extends BaseController
{
    public function __construct(
        protected ShipmentInterface $shipmentRepository,
        protected ShipmentHistoryInterface $shipmentHistoryRepository,
        protected GHTK $shippo
    ) {
    }



    public function index(Request $request, BaseHttpResponse $response)
    {
        $event = $request->input('event');
        $data = (array) $request->input('data', []);

        $transactionId = null;

        switch ($event) {
            case 'transaction_updated':
                $transactionId = Arr::get($data, 'object_id');

                break;
            case 'track_updated':
                $transactionId = Arr::get($data, 'tracking_status.object_id');

                break;
            default:
                $this->shippo->log([__LINE__, print_r($request->input(), true)]);

                break;
        }

        if (!$transactionId) {
            return $response;
        }

        $condition = [
            'tracking_id' => $transactionId,
        ];

        $shipment = $this->shipmentRepository->getFirstBy($condition);

        if (!$shipment) {
            $this->shippo->log([__LINE__, print_r($condition, true)]);

            return $response;
        }

        switch ($event) {
            case 'transaction_updated':
                $this->transactionUpdated($shipment, $data);

                break;
            case 'track_updated':
                $this->trackUpdated($shipment, $data);

                break;
        }

        return $response;
    }

    public function updateShipment(Request $request)
    {
        DB::beginTransaction();
        try {
            $shipment = Shipment::where('shipment_id', $request->label_id)->first();
            $statusTextUpdate = ghtk_match_status_id($request->status_id);

            if (!$shipment) throw new \Exception('Shipment không tồn tại');
            if (!$statusTextUpdate) throw new \Exception('Trạng thái cập nhật không hợp lệ');

            $shipment->update([
                'status' => $statusTextUpdate,
                'updated_at' => $request->action_time
            ]);

            ShipmentHistory::query()->create([
                'action' => 'shipment_updated',
                'description' => 'Đơn vị vận chuyển: ' . ' ' . GHTKStatusEnum::getLabel($statusTextUpdate),
                'order_id' => $shipment->order_id,
                'user_id' => 0,
                'shipment_id' => $shipment->id,
            ]);

            $order = $shipment->order;

            switch ($request->status_id) {
                case 5:
                    // Hoàn thành đơn hàng
                    $order->update([
                        'status' => OrderStatusEnum::COMPLETED,
                        'completed_at' => now()
                    ]);

                    // Cập nhật trạng thái thanh toán
                    $payment = $order->payment;
                    if ($payment) {
                        $payment->update([
                            'status' => PaymentStatusEnum::COMPLETED
                        ]);
                    }

                    // Cập nhật QR SP thành đã bán
                    $qrInOrder = $shipment->order->showroomOrder->list_id_product_qrcode;
                    if (is_array($qrInOrder)) {
                        foreach ($qrInOrder as $qr_id) {
                            ProductQrcode::find($qr_id)->update(['status' => QRStatusEnum::SOLD]);
                        }
                    }
                    break;

                case 6:
                    // Đã đối soát
                    $shipment->update(['cod_status' => ShippingCodStatusEnum::COMPLETED]);
                    break;

                // Sự cố giao hàng
                case 7:
                case 9:
                case 127:
                case 49:
                case 21:
                    $order->update(['status' => OrderStatusEnum::DELIVERY_FAILED]);
                    OrderHistory::query()->create([
                        'action' => 'shipment_update',
                        'description' => 'Đơn vị vận chuyển đã cập nhật: Đơn hàng bị hủy do sự cố giao hàng',
                        'order_id' => $order->getKey(),
                        'user_id' => 0,
                    ]);
                    break;
            }
            DB::commit();
            return response()->json(['error' => 0], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 1], 200);
        }
    }

    protected function transactionUpdated(Shipment $shipment, array $data)
    {
        $status = Arr::get($data, 'status');
        if ($status == 'REFUNDED') {
            $shipment->status = ShippingStatusEnum::CANCELED;
            $shipment->save();
        }

        $this->shipmentHistoryRepository->createOrUpdate([
            'action' => 'transaction_updated',
            'description' => trans('plugins/shippo::shippo.transaction.updated', [
                'tracking' => Arr::get($data, 'tracking_number'),
            ]),
            'order_id' => $shipment->order_id,
            'user_id' => 0,
            'shipment_id' => $shipment->id,
        ]);
    }

    protected function trackUpdated(Shipment $shipment, array $data)
    {
        $status = Arr::get($data, 'tracking_status.status');
        switch ($status) {
            case 'PRE_TRANSIT':

                break;
            case 'TRANSIT':
                $shipment->status = ShippingStatusEnum::DELIVERING;
                $shipment->save();

                break;
            case 'DELIVERED':
                $shipment->status = ShippingStatusEnum::DELIVERED;
                $shipment->date_shipped = Carbon::now();
                $shipment->save();

                OrderHelper::shippingStatusDelivered($shipment, request());

                break;
            case 'RETURNED':
                $shipment->status = ShippingStatusEnum::CANCELED;
                $shipment->save();

                break;
        }

        $this->shipmentHistoryRepository->createOrUpdate([
            'action' => 'track_updated',
            'description' => trans('plugins/shippo::shippo.tracking.statuses.' . Str::lower($status)),
            'order_id' => $shipment->order_id,
            'user_id' => 0,
            'shipment_id' => $shipment->id,
        ]);
    }
}
