<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Requests\AddressRequest;
use Botble\Ecommerce\Http\Requests\ApplyCouponRequest;
use Botble\Ecommerce\Http\Requests\CreateShipmentRequest;
use Botble\Ecommerce\Http\Requests\RefundRequest;
use Botble\Ecommerce\Http\Requests\SearchProductAndVariationsRequest;
use Botble\Ecommerce\Http\Requests\UpdateOrderRequest;
use Botble\Showroom\Http\Resources\AvailableProductResource;
use Botble\Ecommerce\Http\Resources\CartItemResource;
use Botble\Ecommerce\Http\Resources\CustomerAddressResource;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\OrderTaxInformation;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Ecommerce\Models\StoreLocator;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Ecommerce\Services\HandleTaxService;
use Botble\Ecommerce\Tables\OrderIncompleteTable;
use Botble\OrderTransaction\Services\RefundPointService;
use Botble\Payment\Enums\ExtendedPaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Http\Requests\ShowroomAddCustomerWhenCreateOrderRequest;
use Botble\Showroom\Http\Requests\ShowroomCreateOrderRequest;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Models\ShowroomOrderViewEc;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Showroom\Supports\ShowroomOrderHelper;
use Botble\Showroom\Supports\ShowroomOrderVatHelper;
use Botble\Showroom\Tables\ShowroomOrderTable;
use Botble\WarehouseFinishedProducts\Enums\BatchDetailStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;

class ShowroomOrderController extends BaseController
{
    protected $refundPointService;
    public function __construct(
        protected HandleShippingFeeService     $shippingFeeService,
        protected HandleApplyCouponService     $handleApplyCouponService,
        protected HandleApplyPromotionsService $applyPromotionsService,
        RefundPointService $refundPointService
    ) {
        $this->refundPointService = $refundPointService;
    }

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Quản lí đơn hàng', route('showroom.orders.index'));
    }

    public function index(ShowroomOrderTable $dataTable)
    {
        $this->pageTitle(trans('plugins/showroom::order.menu'));

        Assets::addScriptsDirectly([
            'vendor/core/plugins/showroom/js/payment-customer.js'
        ]);

        return $dataTable->renderTable();
    }

    public function create()
    {
        $showroomList = get_showroom_for_user()->pluck('name', 'id');
        if ($showroomList->isEmpty()) {
            return redirect()->route('dashboard.index');
        }

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/showroom/js/showroom-order-create.js',
            ])
            ->addScripts(['input-mask']);

        Assets::usingVueJS();

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        $this->pageTitle(trans('plugins/showroom::order.create'));

        return view('plugins/showroom::orders.create');
    }

    public function store(ShowroomCreateOrderRequest $request)
    {
        DB::beginTransaction();
        try{

            if (empty($request->products)) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage('Vui lòng chọn sản phẩm');
            }
            $data = $this->getDataBeforeCreateOrder($request);
            if (Arr::get($data, 'error')) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage(implode('; ', Arr::get($data, 'message', [])));
            }

            $customerId = Arr::get($data, 'customer_id') ?: 0;
            $userId = Auth::id();
            $paymentStatus = $request->input('payment_status');
            $status = $paymentStatus == 'pending' ? QRStatusEnum::PENDINGSOLD() : QRStatusEnum::SOLD();
            $request->merge([
                'amount' => Arr::get($data, 'total_amount'),
                'user_id' => $customerId,
                'shipping_method' => Arr::get($data, 'shipping_method') ?: ShippingMethodEnum::DEFAULT,
                'shipping_option' => Arr::get($data, 'shipping_option'),
                'shipping_amount' => Arr::get($data, 'shipping_amount'),
                'tax_amount' => Arr::get($data, 'tax_amount') ?: 0,
                'sub_total' => Arr::get($data, 'sub_amount') ?: 0,
                'coupon_code' => Arr::get($data, 'coupon_code'),
                'discount_amount' => Arr::get($data, 'discount_amount') ?: 0,
                'promotion_amount' => Arr::get($data, 'promotion_amount') ?: 0,
                'discount_description' => $request->input('discount_description'),
                'description' => $request->input('note'),
                'is_confirmed' => 1,
                'is_finished' => 1,
                'status' => OrderStatusEnum::PROCESSING,
                'has_refund_point' => Arr::get($data, 'sub_amount') > Arr::get($data, 'total_amount') ? 0 : 1,
            ]);
            if ($paymentStatus != 'pending') {
                $request->merge([
                    'completed_at' => Carbon::today(),
                    'status' => OrderStatusEnum::COMPLETED,
                ]);
            }

            $order = Order::query()->create($request->input());

            $qrCodeList = array_merge(...array_values($request->qr_pro_id));
            $idProductQrcodeList = ProductQrcode::query()->whereIn('qr_code', $qrCodeList)->get()->pluck('id');

            if ($order) {
                $showroomOrder = ShowroomOrder::query()->create([
                    'order_id' => $order->id,
                    'where_type' => Showroom::class,
                    'where_id' => $request->showroom_id,
                    'list_id_product_qrcode' => $idProductQrcodeList,
                ]);

                try {
                    $arrNoti = [
                        'action' => ' tạo ',
                        'permission' => "showroom.orders.edit",
                        'route' => route('showroom.orders.edit', $order),
                        'status' => 'Đã tạo đơn'
                    ];
                    send_notify_cms_and_tele($showroomOrder, $arrNoti);
                } catch (Exception $e) {
                }

                // ProductBatchDetail::query()->whereIn('qrcode_id', $idProductQrcodeList)->update(['status' => BatchDetailStatusEnum::SOLD]);

                $totalProducts = ProductQrcode::whereIn('id', $idProductQrcodeList)
                                    ->select('reference_id', DB::raw('count(*) as total'))
                                    ->groupBy('reference_id')
                                    ->get();
                ProductQrcode::whereIn('qr_code', $qrCodeList)->get()->each(function ($qrcode) use ($status) {
                    $qrcode->status = $status;
                    $qrcode->save();
                });
                forEach($totalProducts as $totalProduct){
                    ShowroomProduct::where('product_id', $totalProduct->reference_id)
                        ->decrement('quantity_qrcode', $totalProduct->total);
                }
                $this->checkProductBatch($idProductQrcodeList);
                OrderHistory::query()->create([
                    'action' => 'create_order_from_admin_page',
                    'description' => trans('plugins/showroom::order.create_order_from_admin_page'),
                    'order_id' => $order->getKey(),
                ]);

                OrderHistory::query()->create([
                    'action' => 'create_order',
                    'description' => trans(
                        'plugins/showroom::order.new_order',
                        ['order_id' => $order->code]
                    ),
                    'order_id' => $order->getKey(),
                ]);

                OrderHistory::query()->create([
                    'action' => 'confirm_order',
                    'description' => trans('plugins/showroom::order.order_was_verified_by'),
                    'order_id' => $order->getKey(),
                    'user_id' => $userId,
                ]);

                if (is_plugin_active('payment')) {
                    if ($request->input('payment_method') == 'bank_transfer') {
                        $paymentStatus = PaymentStatusEnum::PENDING;
                    }
                    $payment = Payment::query()->create([
                        'amount' => $order->amount,
                        'currency' => cms_currency()->getDefaultCurrency()->title,
                        'payment_channel' => $request->input('payment_method'),
                        'status' => $paymentStatus ?: PaymentStatusEnum::PENDING,
                        'payment_type' => 'confirm',
                        'order_id' => $order->id,
                        'charge_id' => Str::upper(Str::random(10)) . $order->id,
                        'user_id' => $userId,
                        'customer_type' => Showroom::class,
                    ]);

                    $order->payment_id = $payment->id;
                    $order->save();

                    if ($paymentStatus == PaymentStatusEnum::COMPLETED) {
                        event(new OrderPaymentConfirmedEvent($order, Auth::user()));
                        if($order->has_refund_point){
                            $this->refundPointService->refundPoint($payment->id,$order);
                        }

                        OrderHistory::query()->create([
                            'action' => 'confirm_payment',
                            'description' => trans('plugins/showroom::order.payment_was_confirmed_by', [
                                'money' => format_price($order->amount),
                            ]),
                            'order_id' => $order->id,
                            'user_id' => $userId,
                        ]);
                    }
                }
                if ($request->input('customer')) {
                    OrderAddress::query()->create([
                        'name' => $request->input('customer.name'),
                        'phone' => $request->input('customer.phone'),
                        'email' => $request->input('customer.email'),
                        'state' => $request->input('customer.state'),
                        'city' => $request->input('customer.city'),
                        'zip_code' => $request->input('customer.zip_code'),
                        'country' => $request->input('customer.country'),
                        'address' => $request->input('customer.address'),
                        'order_id' => $order->id,
                        'type' => OrderAddressTypeEnum::BILLING
                    ]);
                }
                // if ($request->input('customer_address.name')) {
                //     OrderAddress::query()->create([
                //         'name' => $request->input('customer_address.name'),
                //         'phone' => $request->input('customer_address.phone'),
                //         'email' => $request->input('customer_address.email'),
                //         'state' => $request->input('customer_address.state'),
                //         'city' => $request->input('customer_address.city'),
                //         'zip_code' => $request->input('customer_address.zip_code'),
                //         'country' => $request->input('customer_address.country'),
                //         'address' => $request->input('customer_address.address'),
                //         'order_id' => $order->id,
                //         'type' => OrderAddressTypeEnum::BILLING
                //     ]);
                // } elseif ($customerId) {
                //     $customer = Customer::query()->findOrFail($customerId);
                //     OrderAddress::query()->create([
                //         'name' => $customer->name,
                //         'phone' => $request->input('customer_address.phone') ?: $customer->phone,
                //         'email' => $customer->email,
                //         'order_id' => $order->id,
                //     ]);
                // }

                foreach (Arr::get($data, 'products') as $productItem) {
                    $productItem = $productItem->toArray($request);
                    // dd(json_encode((object) Arr::get($request->qr_pro_id, Arr::get($productItem, 'id'))));
                    $quantity = Arr::get($productItem, 'quantity', 1);
                    $orderProduct = [
                        'order_id' => $order->id,
                        'product_id' => Arr::get($productItem, 'id'),
                        'product_name' => Arr::get($productItem, 'name'),
                        'product_image' => Arr::get($productItem, 'image'),
                        'qty' => $quantity,
                        'weight' => Arr::get($productItem, 'weight'),
                        'price' => Arr::get($productItem, 'original_price'),
                        'tax_amount' => Arr::get($productItem, 'tax_price'),
                        // 'product_options' => json_encode((object) Arr::get($request->qr_pro_id, Arr::get($productItem, 'id'))),
                        'product_options' => Arr::get($productItem, 'cart_options.options'),
                        'options' => Arr::get($productItem, 'cart_options', []),
                        'product_type' => Arr::get($productItem, 'product_type'),
                    ];

                    OrderProduct::query()->create($orderProduct);

                    $product = Product::query()->find(Arr::get($productItem, 'id'));
                    if (!$product) {
                        continue;
                    }

                    $ids = [$product->getKey()];
                    if ($product->is_variation && $product->original_product) {
                        $ids[] = $product->original_product->id;
                    }

                    Product::query()
                        ->whereIn('id', $ids)
                        ->where('with_storehouse_management', 1)
                        ->where('quantity', '>=', $quantity)
                        ->decrement('quantity', $quantity);

                    event(new ProductQuantityUpdatedEvent($product));
                }

                // event(new OrderCreated($order));
            }

            // if (Arr::get($data, 'is_available_shipping')) {
            //     $order->load(['shipment']);
            //     $shipment = $order->shipment;
            //     $shippingData = Arr::get($data, 'shipping');

            //     $shipment->update([
            //         'order_id' => $order->id,
            //         'user_id' => 0,
            //         'weight' => Arr::get($data, 'weight') ?: 0,
            //         'cod_amount' => (is_plugin_active(
            //             'payment'
            //         ) && $order->payment->id && $order->payment->status != PaymentStatusEnum::COMPLETED) ? $order->amount : 0,
            //         'cod_status' => ShippingCodStatusEnum::PENDING,
            //         'type' => $order->shipping_method,
            //         'status' => ShippingStatusEnum::PENDING,
            //         'price' => $order->shipping_amount,
            //         'rate_id' => Arr::get($shippingData, 'id', ''),
            //         'shipment_id' => Arr::get($shippingData, 'shipment_id', ''),
            //         'shipping_company_name' => Arr::get($shippingData, 'company_name', ''),
            //     ]);
            // } else {
            //     $order->shipment()->delete();
            // }

            if ($couponCode = $request->input('coupon_code')) {
                Discount::getFacadeRoot()->afterOrderPlaced($couponCode, $customerId);
            }

            if (
                !Arr::get($data, 'is_available_shipping')
                && $paymentStatus === (is_plugin_active('payment') ? PaymentStatusEnum::COMPLETED : 'completed')
            ) {
                OrderHelper::setOrderCompleted($order->getKey(), $request, $userId);
            }
            // $showroomUser = get_agent_for_user();
            // $showroomOrder = [
            //     'order_id' => $order->id,
            //     'where_type' => Showroom::class,
            // ];
            // Create event for admin
            DB::commit();
            return $this
                ->httpResponse()
                ->setData($order)
                ->withCreatedSuccessMessage();
        }
        catch (Exception $e) {
            DB::rollBack();
            return $this
                ->setError()
                ->setMessage(trans('Thanh toán thất bại!!!'));
        }

    }

    public function checkProductBatch($productQr){
        $productBatchDetails = ProductBatchDetail::whereIn('qrcode_id', $productQr)->get();

        $batchQuantities = [];

        foreach ($productBatchDetails as $detail) {
            if (!array_key_exists($detail->batch_id, $batchQuantities)) {
                $batchQuantities[$detail->batch_id] = 1;
            } else {
                $batchQuantities[$detail->batch_id]++;
            }

            $detail->delete();
        }

        foreach ($batchQuantities as $batchId => $quantity) {
            ProductBatch::where('id', $batchId)->decrement('quantity', $quantity);
        }
    }

    public function edit(Order $order)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/showroom/js/order.js',
                'vendor/core/plugins/showroom/js/confirm-return-product.js',
            ])
            ->addScripts(['input-mask']);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        $order->load(['products', 'user', 'shippingAddress', 'taxInformation']);
        $showroomOrder = ShowroomOrder::query()
            ->where('order_id', $order->id)
            ->with(['where' => function ($query) {
                $query->select('id', 'name');
            }])
            ->first();
        $showroom = $showroomOrder?->where;
        $warehouseIds = $showroom->warehouses()->pluck('id');
        $this->pageTitle(trans('plugins/showroom::order.edit_order', ['code' => $order->code]));

        $weight = number_format(EcommerceHelper::validateOrderWeight($order->products_weight));

        $defaultStore = get_primary_store_locator();

        return view('plugins/showroom::orders.edit', compact('order', 'weight', 'defaultStore', 'showroom', 'warehouseIds', 'showroomOrder'));
    }

    public function update(Order $order, UpdateOrderRequest $request)
    {
        $order->fill($request->input());
        $order->save();

        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, $request, $order));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('orders.index'))
            ->withUpdatedSuccessMessage();
    }

    public function addQr(Request $request)
    {
        DB::beginTransaction();
        try {
            $showroomOrder = ShowroomOrder::find($request->showroom_order_id);
            if (!$showroomOrder) throw new \Exception('Cập nhật thất bại');

            $showroomOrder->update([
                'list_id_product_qrcode' => $request->qr_ids,
            ]);

            foreach ($request->qr_ids as $qr_id) {
                ProductQrcode::find($qr_id)->update(['status' => QRStatusEnum::SHIPPING]);
            }
            DB::commit();
            return $this->httpResponse()
                ->setMessage('Cập nhật thành công');

        } catch (\Exception $e) {
            DB::rollback();
            $this->httpResponse()
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function destroy(Order $order, Request $request)
    {
        try {
            $order->delete();
            event(new DeletedContentEvent(ORDER_MODULE_SCREEN_NAME, $request, $order));

            return $this
                ->httpResponse()
                ->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getGenerateInvoice(Order $order, Request $request)
    {
        if (!$order->isInvoiceAvailable()) {
            abort(404);
        }

        if ($request->input('type') == 'print') {
            return InvoiceHelper::streamInvoice($order->invoice);
        }

        return InvoiceHelper::downloadInvoice($order->invoice);
    }

    public function postConfirm(Request $request)
    {
        $order = Order::query()->findOrFail($request->input('order_id'));
        $order->is_confirmed = 1;
        if ($order->status == OrderStatusEnum::PENDING) {
            $order->status = OrderStatusEnum::PROCESSING;
        }

        $order->save();

        OrderHistory::query()->create([
            'action' => 'confirm_order',
            'description' => trans('plugins/showroom::order.order_was_verified_by'),
            'order_id' => $order->getKey(),
            'user_id' => Auth::id(),
        ]);

        $payment = Payment::query()->where('order_id', $order->getKey())->first();

        if ($payment) {
            $payment->user_id = Auth::id();
            $payment->save();
        }

        event(new OrderConfirmedEvent($order, Auth::user()));

        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('order_confirm')) {
            OrderHelper::setEmailVariables($order);
            $mailer->sendUsingTemplate(
                'order_confirm',
                $order->user->email ?: $order->address->email
            );
        }

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/showroom::order.confirm_order_success'));
    }

    public function postResendOrderConfirmationEmail(Order $order)
    {
        $result = OrderHelper::sendOrderConfirmationEmail($order);

        if (!$result) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/showroom::order.error_when_sending_email'));
        }

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/showroom::order.sent_confirmation_email_success'));
    }

    public function getShipmentForm(
        Order                    $order,
        HandleShippingFeeService $shippingFeeService,
        Request                  $request
    ) {
        if ($request->has('weight')) {
            $weight = $request->input('weight');
        } else {
            $weight = $order->products_weight;
        }

        $shippingData = [
            'address' => $order->address->address,
            'country' => $order->address->country,
            'state' => $order->address->state,
            'city' => $order->address->city,
            'weight' => $weight,
            'order_total' => $order->amount,
        ];

        $shipping = $shippingFeeService->execute($shippingData);

        $storeLocators = StoreLocator::query()->where('is_shipping_location', true)->get();

        $url = route('orders.create-shipment', $order->getKey());

        if ($request->has('view')) {
            return view(
                'plugins/showroom::orders.shipment-form',
                compact('order', 'weight', 'shipping', 'storeLocators', 'url')
            );
        }

        return $this
            ->httpResponse()->setData(
                view(
                    'plugins/showroom::orders.shipment-form',
                    compact('order', 'weight', 'shipping', 'storeLocators', 'url')
                )->render()
            );
    }

    public function postCreateShipment(Order $order, CreateShipmentRequest $request)
    {
        $result = $this->httpResponse();

        $shipment = [
            'order_id' => $order->getKey(),
            'user_id' => Auth::id(),
            'weight' => $order->products_weight,
            'note' => $request->input('note'),
            'cod_amount' => $request->input('cod_amount') ?? (is_plugin_active(
                'payment'
            ) && $order->payment->status != PaymentStatusEnum::COMPLETED ? $order->amount : 0),
            'cod_status' => 'pending',
            'type' => $request->input('method'),
            'status' => ShippingStatusEnum::DELIVERING,
            'price' => $order->shipping_amount,
            'store_id' => $request->input('store_id'),
        ];

        $store = StoreLocator::query()->find($request->input('store_id'));

        if (!$store) {
            $shipment['store_id'] = StoreLocator::query()->where('is_primary', true)->value('id');
        }

        $result = $result->setMessage(trans('plugins/showroom::order.order_was_sent_to_shipping_team'));

        if (!$result->isError()) {
            $order->fill([
                'status' => OrderStatusEnum::PROCESSING,
                'shipping_method' => $request->input('method'),
                'shipping_option' => $request->input('option'),
            ]);
            $order->save();

            $shipment = Shipment::query()->create($shipment);

            OrderHistory::query()->create([
                'action' => 'create_shipment',
                'description' => $result->getMessage() . ' ' . trans('plugins/showroom::order.by_username'),
                'order_id' => $order->getKey(),
                'user_id' => Auth::id(),
            ]);

            ShipmentHistory::query()->create([
                'action' => 'create_from_order',
                'description' => trans('plugins/showroom::order.shipping_was_created_from'),
                'shipment_id' => $shipment->id,
                'order_id' => $order->getKey(),
                'user_id' => Auth::id(),
            ]);
        }

        return $result;
    }

    public function postCancelShipment(Shipment $shipment)
    {
        $shipment->update(['status' => ShippingStatusEnum::CANCELED]);

        OrderHistory::query()->create([
            'action' => 'cancel_shipment',
            'description' => trans('plugins/showroom::order.shipping_was_canceled_by'),
            'order_id' => $shipment->order_id,
            'user_id' => Auth::id(),
        ]);

        return $this
            ->httpResponse()
            ->setData([
                'status' => ShippingStatusEnum::CANCELED,
                'status_text' => ShippingStatusEnum::CANCELED()->label(),
            ])
            ->setMessage(trans('plugins/showroom::order.shipping_was_canceled_success'));
    }

    public function postUpdateShippingAddress(OrderAddress $address, AddressRequest $request)
    {
        $address->fill($request->input());
        $address->save();

        if ($address->order->status == OrderStatusEnum::CANCELED) {
            abort(401);
        }

        return $this
            ->httpResponse()
            ->setData([
                'line' => view('plugins/showroom::orders.shipping-address.line', compact('address'))->render(),
                'detail' => view('plugins/showroom::orders.shipping-address.detail', compact('address'))->render(),
            ])
            ->setMessage(trans('plugins/showroom::order.update_shipping_address_success'));
    }

    public function postUpdateTaxInformation(OrderTaxInformation $taxInformation, Request $request)
    {
        $validated = $request->validate([
            'company_tax_code' => 'required|string|min:3|max:20',
            'company_name' => 'required|string|min:3|max:120',
            'company_address' => 'required|string|min:3|max:255',
            'company_email' => 'required|email|min:6|max:60',
        ]);

        $taxInformation->load(['order']);

        $taxInformation->update($validated);

        if ($taxInformation->order->status === OrderStatusEnum::CANCELED) {
            abort(401);
        }

        return $this
            ->httpResponse()
            ->setData(view('plugins/showroom::orders.tax-information.detail', ['tax' => $taxInformation])->render())
            ->setMessage(trans('plugins/showroom::order.tax_info.update_success'));
    }

    public function postCancelOrder(Order $order)
    {
        // if (!$order->canBeCanceledByAdmin()) {
        //     abort(403);
        // }
        // if ($order->status) {
        //     $order->status = OrderStatusEnum::CANCELED();
        // }

        OrderHelper::cancelOrder($order);

        OrderHistory::query()->create([
            'action' => 'cancel_order',
            'description' => trans('plugins/showroom::order.order_was_canceled_by'),
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);
        // $order->save();
        if (is_plugin_active('payment')) {
            Payment::query()->where('order_id', $order->id)->update([
                'status' => PaymentStatusEnum::FAILED,
            ]);
        }

        if (isset($order->id)) {
            $this->updateStatusProductQrcode($order->id, QRStatusEnum::INSTOCK);
        }


        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/showroom::order.cancel_success'));
    }

    public function postConfirmPayment(Order $order, RefundPointService $refundPointService)
    {
        DB::beginTransaction();
        try {
            if (isset($order->id)) {
                $this->updateStatusProductQrcode($order->id, QRStatusEnum::SOLD);
            }
            if ($order->status == OrderStatusEnum::PENDING || $order->status == OrderStatusEnum::PROCESSING) {
                if($order->shipment?->id)
                    {
                        $order->status = OrderStatusEnum::PROCESSING;
                    }
                else
                    {
                        $order->status = OrderStatusEnum::COMPLETED;
                        $order->completed_at = Carbon::now();
                    }

            }
            if($order->has_refund_point){
                $refundPointService->refundPoint($order->payment->id,$order);
            }
            $order->save();

            $order->load(['payment']);

            OrderHelper::confirmPayment($order);

            DB::commit();
            return $this
                ->httpResponse()
                ->setMessage(trans('plugins/showroom::order.confirm_payment_success'));
        } catch (Exception  $e) {
            DB::rollBack();
            throw new Exception('SHOWROOM_MODULE_SCREEN_NAME:' + $e);
        }
    }

    private function updateStatusProductQrcode($orderId, $status)
    {
        $idProductQrcodeList = ShowroomOrder::query()
            ->where('order_id', $orderId)
            ->select('list_id_product_qrcode')
            ->first();
        if ($idProductQrcodeList) {
            ProductQrcode::query()->whereIn('id', $idProductQrcodeList->list_id_product_qrcode)->update(['status' => $status]);
        }
    }

    public function postConfirmQrcodePayment(Order $order)
    {

        $qrCodePayment = QrCode::size(150)->format('png')->errorCorrection('H')->generate($order->code);

        $qrCodeBase64 = base64_encode($qrCodePayment);

        return response()->json(['qrCode' => $qrCodeBase64]);

        // if($paymentStatus != 'pending'){
        //     $request->merge([
        //         'completed_at' => Carbon::today(),
        //         'status' => OrderStatusEnum::COMPLETED,
        //     ]);
        // }

        // $order = Order::query()->create($request->input());

        if ($order->status === OrderStatusEnum::PENDING) {
            $order->status = OrderStatusEnum::PROCESSING;
        }

        $order->save();

        $order->load(['payment']);

        OrderHelper::confirmPayment($order);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/showroom::order.confirm_payment_success'));
    }

    public function postRefund(Order $order, RefundRequest $request)
    {
        if (is_plugin_active('payment') && $request->input(
            'refund_amount'
        ) > ($order->payment->amount - $order->payment->refunded_amount)) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(
                    trans('plugins/showroom::order.refund_amount_invalid', [
                        'price' => format_price(
                            $order->payment->amount - $order->payment->refunded_amount,
                            get_application_currency()
                        ),
                    ])
                );
        }

        foreach ($request->input('products', []) as $productId => $quantity) {
            $orderProduct = OrderProduct::query()->where([
                'product_id' => $productId,
                'order_id' => $order->getKey(),
            ])
                ->first();

            if ($quantity > ($orderProduct->qty - $orderProduct->restock_quantity)) {
                $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage(trans('plugins/showroom::order.number_of_products_invalid'));

                break;
            }
        }

        $response = apply_filters(ACTION_BEFORE_POST_ORDER_REFUND_ECOMMERCE, $this->httpResponse(), $order, $request);

        if ($response->isError()) {
            return $this->httpResponse();
        }

        $payment = $order->payment;
        if (!$payment) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/showroom::order.cannot_found_payment_for_this_order'));
        }

        $refundAmount = $request->input('refund_amount');

        if ($paymentService = get_payment_is_support_refund_online($payment)) {
            $paymentResponse = (new $paymentService());
            if (method_exists($paymentService, 'setCurrency')) {
                $paymentResponse = $paymentResponse->setCurrency($payment->currency);
            }

            $optionRefunds = [
                'refund_note' => $request->input('refund_note'),
                'order_id' => $order->getKey(),
            ];

            $paymentResponse = $paymentResponse->refundOrder($payment->charge_id, $refundAmount, $optionRefunds);

            if (Arr::get($paymentResponse, 'error', true)) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage(Arr::get($paymentResponse, 'message', ''));
            }

            if (Arr::get($paymentResponse, 'data.refund_redirect_url')) {
                return $this
                    ->httpResponse()
                    ->setNextUrl($paymentResponse['data']['refund_redirect_url'])
                    ->setData($paymentResponse['data'])
                    ->setMessage(Arr::get($paymentResponse, 'message', ''));
            }

            $refundData = (array)Arr::get($paymentResponse, 'data', []);

            $response->setData($refundData);

            $refundData['_data_request'] = $request->except(['_token']) + [
                'currency' => $payment->currency,
                'created_at' => Carbon::now(),
            ];
            $metadata = $payment->metadata;
            $refunds = Arr::get($metadata, 'refunds', []);
            $refunds[] = $refundData;
            Arr::set($metadata, 'refunds', $refunds);

            $payment->metadata = $metadata;
        }

        $payment->refunded_amount += $refundAmount;

        if ($payment->refunded_amount == $payment->amount) {
            $payment->status = PaymentStatusEnum::REFUNDED;
        }

        $payment->refund_note = $request->input('refund_note');
        $payment->save();

        foreach ($request->input('products', []) as $productId => $quantity) {
            $product = Product::query()->find($productId);

            if ($product && $product->with_storehouse_management) {
                $product->quantity += $quantity;
                $product->save();
            }

            $orderProduct = OrderProduct::query()->where([
                'product_id' => $productId,
                'order_id' => $order->getKey(),
            ])
                ->first();

            if ($orderProduct) {
                $orderProduct->restock_quantity += $quantity;
                $orderProduct->save();
            }
        }

        if ($refundAmount > 0) {
            OrderHistory::query()->create([
                'action' => 'refund',
                'description' => trans('plugins/showroom::order.refund_success_with_price', [
                    'price' => format_price($refundAmount),
                ]),
                'order_id' => $order->getKey(),
                'user_id' => Auth::id(),
                'extras' => json_encode([
                    'amount' => $refundAmount,
                    'method' => $payment->payment_channel ?? ExtendedPaymentMethodEnum::COD,
                ]),
            ]);
        }

        $response->setMessage(trans('plugins/showroom::order.refund_success'));

        return apply_filters(ACTION_AFTER_POST_ORDER_REFUNDED_ECOMMERCE, $response, $order, $request);
    }

    public function getAvailableShippingMethods(Request $request, HandleShippingFeeService $shippingFeeService)
    {
        $weight = 0;
        $orderAmount = 0;

        foreach ($request->input('products', []) as $productId) {
            $product = Product::query()->find($productId);
            if ($product) {
                $weight += $product->weight * $product->qty;
                $orderAmount += $product->front_sale_price;
            }
        }

        $weight = EcommerceHelper::validateOrderWeight($weight);

        $shippingData = [
            'address' => $request->input('address'),
            'country' => $request->input('country'),
            'state' => $request->input('state'),
            'city' => $request->input('city'),
            'weight' => $weight,
            'order_total' => $orderAmount,
        ];

        $shipping = $shippingFeeService->execute($shippingData);

        $result = [];
        foreach ($shipping as $key => $shippingItem) {
            foreach ($shippingItem as $subKey => $subShippingItem) {
                $result[$key . ';' . $subKey . ';' . $subShippingItem['price']] = [
                    'name' => $subShippingItem['name'],
                    'price' => format_price($subShippingItem['price'], null, true),
                ];
            }
        }

        return $this
            ->httpResponse()
            ->setData($result);
    }

    public function postApplyCoupon(ApplyCouponRequest $request, HandleApplyCouponService $handleApplyCouponService)
    {
        $result = $handleApplyCouponService->applyCouponWhenCreatingOrderFromAdmin($request);

        if ($result['error']) {
            return $this
                ->httpResponse()
                ->setError()
                ->withInput()
                ->setMessage($result['message']);
        }

        return $this
            ->httpResponse()
            ->setData(Arr::get($result, 'data', []))
            ->setMessage(
                trans(
                    'plugins/showroom::order.applied_coupon_success',
                    ['code' => $request->input('coupon_code')]
                )
            );
    }

    public function getReorder(Request $request)
    {
        if (!$request->input('order_id')) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(route('orders.index'))
                ->setMessage(trans('plugins/showroom::order.order_is_not_existed'));
        }

        $this->pageTitle(trans('plugins/showroom::order.reorder'));

        Assets::usingVueJS();

        $order = Order::query()->find($request->input('order_id'));

        if (!$order) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(route('orders.index'))
                ->setMessage(trans('plugins/showroom::order.order_is_not_existed'));
        }

        $productIds = $order->products->pluck('product_id')->all();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get();

        $cartItems = collect();
        foreach ($order->products as $orderProduct) {
            $product = $products->firstWhere('id', $orderProduct->product_id);
            //            if (!$product) {
            //                continue;
            //            }

            $options = [
                'options' => $orderProduct->product_options,
            ];

            $cartItem = CartItem::fromAttributes($product->id, $orderProduct->product_name, 0, $options);
            $cartItem->setQuantity($orderProduct ? $orderProduct->qty : 1);

            $cartItems[] = $cartItem;
        }

        $products = CartItemResource::collection($cartItems);

        $customer = null;
        $customerAddresses = [];
        $customerOrderNumbers = 0;
        if ($order->user_id) {
            $customer = Customer::query()->findOrFail($order->user_id);
            $customer->avatar = (string)$customer->avatar_url;

            if ($customer) {
                $customerOrderNumbers = $customer->orders()->count();
            }

            $customerAddresses = CustomerAddressResource::collection($customer->addresses);
        }

        $customerAddress = new CustomerAddressResource($order->address);

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order-create.js',
            ])
            ->addScripts(['input-mask']);

        return view(
            'plugins/showroom::orders.reorder',
            compact(
                'order',
                'products',
                'productIds',
                'customer',
                'customerAddresses',
                'customerAddress',
                'customerOrderNumbers'
            )
        );
    }

    public function getIncompleteList(OrderIncompleteTable $dataTable)
    {
        $this->pageTitle(trans('plugins/showroom::order.incomplete_order'));

        return $dataTable->renderTable();
    }

    public function getViewIncompleteOrder(Order $order)
    {
        $this->pageTitle(trans('plugins/showroom::order.incomplete_order'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order-incomplete.js',
            ]);

        $order->load(['products', 'user']);

        $weight = number_format(EcommerceHelper::validateOrderWeight($order->products_weight));

        return view('plugins/showroom::orders.view-incomplete-order', compact('order', 'weight'));
    }

    public function markIncompleteOrderAsCompleted(Order $order)
    {
        DB::transaction(function () use ($order) {
            $order->update(['is_finished' => true]);

            $order->histories()->create([
                'order_id' => $order->getKey(),
                'user_id' => Auth::user()->getKey(),
                'action' => 'mark_order_as_completed',
                'description' => trans('plugins/showroom::order.mark_as_completed.history', [
                    'admin' => Auth::user()->name,
                    'time' => Carbon::now(),
                ]),
            ]);
        });

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/showroom::order.mark_as_completed.success'))
            ->setData([
                'next_url' => route('orders.edit', $order->getKey()),
            ]);
    }

    public function postSendOrderRecoverEmail(Order $order)
    {
        $email = $order->user->email ?: $order->address->email;

        if (!$email) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/showroom::order.error_when_sending_email'));
        }

        try {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);

            $order->dont_show_order_info_in_product_list = true;
            OrderHelper::setEmailVariables($order);

            $mailer->sendUsingTemplate('order_recover', $email);

            return $this
                ->httpResponse()->setMessage(trans('plugins/showroom::order.sent_email_incomplete_order_success'));
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function checkDataBeforeCreateOrder(Request $request)
    {
        $data = $this->getDataBeforeCreateOrder($request);
        return $this
            ->httpResponse()
            ->setData($data)
            ->setError(Arr::get($data, 'error', false))
            ->setMessage(implode('; ', Arr::get($data, 'message', [])));
    }

    protected function getDataBeforeCreateOrder(Request $request): array
    {
        if ($customerId = $request->input('customer_id')) {
            Discount::getFacadeRoot()->setCustomerId($customerId);
        }

        $with = [
            'productCollections',
            'variationInfo',
            'variationInfo.configurableProduct',
            'variationProductAttributes',
        ];
        if (is_plugin_active('marketplacce')) {
            $with = array_merge($with, ['store', 'variationInfo.configurableProduct.store']);
        }

        $inputProducts = collect($request->input('products'));
        if ($productIds = $inputProducts->pluck('id')->all()) {
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->with($with)
                ->get();
        } else {
            $products = collect();
        }

        $weight = 0;
        $discountAmount = 0;
        $shippingAmount = 0;
        $isError = false;
        $message = [];

        $cartItems = collect();
        $stores = collect();
        $productItems = collect();
        $addressKeys = ['name', 'company', 'address', 'country', 'state', 'city', 'zip_code', 'email', 'phone'];
        $addressTo = Arr::only($request->input('customer_address', []), $addressKeys);
        $country = Arr::get($addressTo, 'country');
        $state = Arr::get($addressTo, 'state');
        $city = Arr::get($addressTo, 'city');
        $zipCode = Arr::get($addressTo, 'zip_code');
        foreach ($inputProducts as $inputProduct) {
            $productId = $inputProduct['id'];
            $product = $products->firstWhere('id', $productId);

            if (!$product) {
                continue;
            }
            $productName = $product->original_product->name ?: $product->name;
            $productOptions = [];
            if ($inputOptions = Arr::get($inputProduct, 'options') ?: []) {
                $productOptions = OrderHelper::getProductOptionData($inputOptions);
            }

            $cartItemsById = $cartItems->where('id', $productId);
            $inputQty = Arr::get($inputProduct, 'quantity') ?: 1;
            $qty = $inputQty;
            $qtySelected = 0;
            if ($cartItemsById->count()) {
                $qtySelected = $cartItemsById->sum('qty');
            }

            $originalQuantity = $product->quantity;
            $product->quantity = (int)$product->quantity - $qtySelected - $inputQty + 1;

            if ($product->quantity < 0) {
                $product->quantity = 0;
            }

            $product->quantity = $originalQuantity;

            if ($product->original_product->options->where('required', true)->count()) {
                if ($inputOptions) {
                    $requiredOptions = $product->original_product->options->where('required', true);

                    foreach ($requiredOptions as $requiredOption) {
                        if (!Arr::get($inputOptions, $requiredOption->id . '.values')) {
                            $isError = true;
                            $message[] = trans(
                                'plugins/showroom::product-option.add_to_cart_value_required',
                                ['value' => $requiredOption->name]
                            );
                        }
                    }
                }
            }

            if (is_plugin_active('marketplace')) {
                $store = $product->original_product->store;
                if ($store->id) {
                    $productName .= ' (' . $store->name . ')';
                }
                $stores[] = $store;
            }

            $parentProduct = $product->original_product;

            $image = $product->image ?: $parentProduct->image;
            $taxRate = app(HandleTaxService::class)->taxRate($parentProduct, $country, $state, $city, $zipCode);
            $options = [
                'name' => $productName,
                'image' => $image,
                'attributes' => $product->is_variation ? $product->variation_attributes : '',
                'taxRate' => $taxRate,
                'options' => $productOptions,
                'extras' => [],
                'sku' => $product->sku,
                'weight' => $product->original_product->weight,
                'original_price' => $product->original_price,
                'product_link' => route('products.edit', $product->original_product->id),
                'product_type' => (string)$product->product_type,
            ];
            $price = $product->original_price;
            $price = Cart::getPriceByOptions($price, $productOptions);

            $cartItem = CartItem::fromAttributes(
                $product->id,
                BaseHelper::clean($parentProduct->name ?: $product->name),
                $price,
                $options
            );

            $cartItemExists = $cartItems->firstWhere('rowId', $cartItem->rowId);

            if (!$cartItemExists) {
                $cartItem->setQuantity($qty);
                $cartItem->setTaxRate($taxRate);

                $cartItems[] = $cartItem;
                if (!$product->isTypeDigital()) {
                    $weight += $product->original_product->weight * $qty;
                }
                $product->cartItem = $cartItem;
                $productItems[] = $product;
            } else {
                $cartItemExists->setQuantity($cartItemExists->qty + 1);
            }
        }

        if (is_plugin_active('marketplace')) {
            if (count(array_unique(array_filter($stores->pluck('id')->all()))) > 1) {
                $isError = true;
                $message[] = trans('plugins/marketplace::order.products_are_from_different_vendors');
            }
        }

        $subAmount = Cart::rawSubTotalByItems($cartItems);
        $taxAmount = Cart::rawTaxByItems($cartItems);
        $rawTotal = Cart::rawTotalByItems($cartItems);

        $cartData = [];

        Arr::set($cartData, 'rawTotal', $rawTotal);
        Arr::set($cartData, 'cartItems', $cartItems);
        Arr::set($cartData, 'countCart', Cart::countByItems($cartItems));
        Arr::set($cartData, 'productItems', $productItems);

        $isAvailableShipping = $productItems->count() && EcommerceHelper::isAvailableShipping($productItems);

        $weight = EcommerceHelper::validateOrderWeight($weight);

        $shippingMethods = [];

        // if ($isAvailableShipping) {
        //     $origin = EcommerceHelper::getOriginAddress();

        //     if (is_plugin_active('marketplace')) {
        //         if ($stores->count() && ($store = $stores->first()) && $store->id) {
        //             $origin = Arr::only($store->toArray(), $addressKeys);
        //             if (!EcommerceHelper::isUsingInMultipleCountries()) {
        //                 $origin['country'] = EcommerceHelper::getFirstCountryId();
        //             }
        //         }
        //     }

        //     $items = [];
        //     foreach ($productItems as $product) {
        //         if (!$product->isTypeDigital()) {
        //             $cartItem = $product->cartItem;
        //             $items[$cartItem->rowId] = [
        //                 'weight' => $product->weight,
        //                 'length' => $product->length,
        //                 'wide' => $product->wide,
        //                 'height' => $product->height,
        //                 'name' => $product->name,
        //                 'description' => $product->description,
        //                 'qty' => $cartItem->qty,
        //                 'price' => $product->original_price,
        //             ];
        //         }
        //     }

        //     $shippingData = [
        //         'address' => Arr::get($addressTo, 'address'),
        //         'country' => $country,
        //         'state' => $state,
        //         'city' => $city,
        //         'weight' => $weight,
        //         'order_total' => $rawTotal,
        //         'address_to' => $addressTo,
        //         'origin' => $origin,
        //         'items' => $items,
        //         'extra' => [],
        //         'payment_method' => $request->input('payment_method'),
        //     ];

        //     $shipping = $this->shippingFeeService->execute($shippingData);


        //     foreach ($shipping as $key => $shippingItem) {
        //         foreach ($shippingItem as $subKey => $subShippingItem) {
        //             $shippingMethods[$key . ';' . $subKey] = [
        //                 'name' => $subShippingItem['name'],
        //                 'price' => format_price($subShippingItem['price'], null, true),
        //                 'price_label' => format_price($subShippingItem['price']),
        //                 'method' => $key,
        //                 'option' => $subKey,
        //                 'title' => $subShippingItem['name'] . ' - ' . format_price($subShippingItem['price']),
        //                 'id' => Arr::get($subShippingItem, 'id'),
        //                 'shipment_id' => Arr::get($subShippingItem, 'shipment_id'),
        //                 'company_name' => Arr::get($subShippingItem, 'company_name'),
        //             ];

        //         }
        //     }
        // }

        $shippingMethodName = '';
        $shippingMethod = $request->input('shipping_method');
        $shippingOption = $request->input('shipping_option');
        $shippingType = $request->input('shipping_type');
        $shipping = [];

        if ($shippingType == 'free-shipping') {
            $shippingMethodName = trans('plugins/showroom::order.free_shipping');
            $shippingMethod = 'default';
        } else {
            if ($shippingMethod && $shippingOption) {
                if ($shipping = Arr::get($shippingMethods, $shippingMethod . ';' . $shippingOption)) {
                    $shippingAmount = Arr::get($shipping, 'price') ?: 0;
                    $shippingMethodName = Arr::get($shipping, 'name');
                }
            }
            if (!$shippingMethodName) {
                if ($shipping = Arr::first($shippingMethods)) {
                    $shippingAmount = Arr::get($shipping, 'price') ?: 0;
                    $shippingMethodName = Arr::get($shipping, 'name');
                }
            }
            if (!$shippingMethodName) {
                $shippingMethod = 'default';
                $shippingOption = '';
            }
        }

        $promotionAmount = $this->applyPromotionsService->getPromotionDiscountAmount($cartData);

        Arr::set($cartData, 'promotion_discount_amount', $promotionAmount);

        if ($couponCode = trim($request->input('coupon_code'))) {
            $couponData = $this->handleApplyCouponService->applyCouponWhenCreatingOrderFromAdmin($request, $cartData);
            if (Arr::get($couponData, 'error')) {
                $isError = true;
                $message[] = Arr::get($couponData, 'message');
            } else {
                if (Arr::get($couponData, 'data.is_free_shipping')) {
                    $shippingAmount = 0;
                } else {
                    $discountAmount = Arr::get($couponData, 'data.discount_amount');
                    if (!$discountAmount) {
                        $isError = true;
                        $message[] = __('Coupon code is not valid or does not apply to the products');
                    }
                }
            }
        } else {
            $couponData = [];
            if ($discountCustomValue = max((float)$request->input('discount_custom_value'), 0)) {
                if ($request->input('discount_type') === 'percentage') {
                    $discountAmount = $rawTotal * min($discountCustomValue, 100) / 100;
                } else {
                    $discountAmount = $discountCustomValue;
                }
            }
        }

        $totalAmount = max($rawTotal - $promotionAmount - $discountAmount, 0) + $shippingAmount;

        $data = [
            'customer_id' => $customerId,
            'products' => CartItemResource::collection($cartItems),
            'shipping_methods' => $shippingMethods,
            'weight' => $weight,
            'promotion_amount' => $promotionAmount,
            'promotion_amount_label' => format_price($promotionAmount),
            'discount_amount' => $discountAmount,
            'discount_amount_label' => format_price($discountAmount),
            'sub_amount' => $subAmount,
            'sub_amount_label' => format_price($subAmount),
            'tax_amount' => $taxAmount,
            'tax_amount_label' => format_price($taxAmount),
            'shipping_amount' => $shippingAmount,
            'shipping_amount_label' => format_price($shippingAmount),
            'total_amount' => $totalAmount,
            'total_amount_label' => format_price($totalAmount),
            'coupon_data' => $couponData,
            'shipping' => $shipping,
            'shipping_method_name' => $shippingMethodName,
            'shipping_type' => $shippingType,
            'shipping_method' => $shippingMethod,
            'shipping_option' => $shippingOption,
            'coupon_code' => $couponCode,
            'is_available_shipping' => $isAvailableShipping,
            'update_context_data' => true,
            'error' => $isError,
            'message' => $message,
        ];

        if (is_plugin_active('marketplace')) {
            $data['store'] = $stores->first() ?: [];
        }
        return $data;
    }

    public function generateInvoice(Order $order)
    {
        if ($order->isInvoiceAvailable()) {
            abort(404);
        }

        InvoiceHelper::store($order);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/showroom::order.generated_invoice_successfully'));
    }

    public function postCreateCustomerWhenCreatingOrder(ShowroomAddCustomerWhenCreateOrderRequest $request)
    {
        $request->merge(['password' => Hash::make(Str::random(36)), 'email' => 'NULL']);
        $customer = Customer::query()->create($request->input());
        $customer->avatar = (string)$customer->avatar_url;

        event(new CreatedContentEvent(CUSTOMER_MODULE_SCREEN_NAME, $request, $customer));


        return $this
            ->httpResponse()
            ->setData(compact('customer'))
            ->withCreatedSuccessMessage();
    }

    public function viewCheckoutPayment()
    {
        return view('plugins/showroom::orders.checkout-payment');
    }

    public function printShowroomOrder(string|int $id, Request $request, ShowroomOrderHelper $issueHelper)
    {
        $data = ShowroomOrderViewEc::find($id);

        if ($request->type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }

    public function printShowroomOrderVAT(string|int $id, Request $request, ShowroomOrderVatHelper $issueHelper)
    {
        $data = ShowroomOrderViewEc::find($id);

        if ($request->type === 'print') {
            return $issueHelper->streamInvoice($data);
        }
        return $issueHelper->downloadInvoice($data);
    }

    public function getAllProductAndVariations(
        SearchProductAndVariationsRequest $request,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $valueQrcode = array_values($request->input('selected_qrcode', []));

        $mergedvalueQrcode = call_user_func_array('array_merge', $valueQrcode);
        $selectedProducts = collect();
        if ($productIds = $request->input('product_ids', [])) {
            $selectedProducts = Product::query()
                ->wherePublished()
                ->whereIn('id', $productIds)
                ->with(['variationInfo.configurableProduct'])
                ->get();
        }

        $keyword = $request->input('keyword');

        $getIdShowroomWarehouse = ShowroomWarehouse::query()->where('showroom_id', $request->showroom_id)->get()->pluck('id')?->toArray();

        $getQrInKeyWordIds = ProductQrCode::query()
            ->where('status', 'instock')
            ->where('reference_type', Product::class)
            ->where('warehouse_type', ShowroomWarehouse::class)
            ->whereNotIn('qr_code', $mergedvalueQrcode)
            ->whereIn('warehouse_id', $getIdShowroomWarehouse)
            ->with('reference.parentProduct')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('identifier', 'LIKE', '%' . $keyword . '%');
            })
            ->limit(5)->get();

            $parentProductId = [];
            $productQrcodeId = [];
            foreach ($getQrInKeyWordIds as $qrCode) {
                if ($qrCode?->reference && $qrCode?->reference?->parentProduct) {
                    $productQrcodeId[] = $qrCode->reference_id;
                    $parentProduct = $qrCode?->reference?->parentProduct;
                    if (!is_null($parentProduct)) {
                        $parentProductId[] = $parentProduct->first()?->id;
                    }
                }
            }

        $availableProducts = Product::query()
            ->select(['ec_products.*'])
            ->where('is_variation', false)
            ->wherePublished()
            ->with(['variationInfo.configurableProduct'])
            ->whereIn('id', $parentProductId)
            ;

        if (is_plugin_active('marketplace') && $selectedProducts->count()) {
            $selectedProducts = $selectedProducts->map(function ($item) {
                if ($item->is_variation) {
                    $item->store_id = $item->original_product->store_id;
                }

                if (! $item->store_id) {
                    $item->store_id = 0;
                }

                return $item;
            });
            $storeIds = array_unique($selectedProducts->pluck('store_id')->all());
            $availableProducts = $availableProducts->whereIn('store_id', $storeIds)->with(['store']);
        }

        $availableProducts = $availableProducts->simplePaginate(5);

        $res = AvailableProductResource::collection($availableProducts)->response()->getData();


        $filteredData = [];



        function filterVariations($variations, $allowedIds) {
            return array_filter($variations, function ($variation) use ($allowedIds) {
                return in_array($variation->id, $allowedIds);
            });
        }

        // function filterQrcode($qrcodes, $allowedIds) {
        //     $qrcode = array_filter($qrcodes, function ($qrcode) use ($allowedIds) {
        //         if(!empty($qrcode)){
        //             return in_array($qrcode?->reference_id, $allowedIds);
        //         }
        //     });

        //     if(is_array($qrcode) && count($qrcode) > 0){
        //         return array_values($qrcode)[0]->identifier;
        //     }else{
        //         return null;
        //     }
        // }
        $filteredData = [];

        foreach ($res->data as $product) {

            $filteredProduct = clone $product;

            $filteredVariations = filterVariations($product->variations, $productQrcodeId);

            // $filteredQrcode = filterQrcode($product->qrcode, $productQrcodeId);

            $filteredProduct->variations = array_values($filteredVariations);
            // $filteredProduct->qrcode = $filteredQrcode;
            $filteredData[] = $filteredProduct;
        }
        $res->data = $filteredData;
        return $response->setData($res);
    }

    public function getQrcodeInAddSearchProduct(Request $request, BaseHttpResponse $response){
        $keyword = $request->key_search;
        $qrcode = ProductQrCode::query()
            ->where('status', 'instock')
            ->where('reference_type', Product::class)
            ->where('warehouse_type', ShowroomWarehouse::class)
            ->where('reference_id', $request->product_id)
            // ->whereIn('warehouse_id', $getIdShowroomWarehouse)
            ->with('reference.parentProduct')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('identifier', 'LIKE', '%' . $keyword . '%');
            })
            ->select('qr_code')->first();
        return $response->setData($qrcode);
    }
    public function postConfirmBankTransfer(Request $request)
    {
        $orderId = $request->order_id;
        $images = $request->images;
        $contentBanking  = $request->content_banking;
        if (!$orderId || !is_numeric($orderId)) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(__('Order ID is invalid'));
        }
        /** @var Order $order */
        $order = Order::query()->find($orderId);
        if (empty($order)) {        
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(__('Không tìm thấy đơn hàng'));
        }
        if ($order->status == OrderStatusEnum::COMPLETED) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(__('Đơn hàng đã hoàn thành'));
        }
        try{
            if($order->shipment?->id){
                $order->status = OrderStatusEnum::PROCESSING;
            }
            else{
                $order->completed_at = Carbon::now();
                $order->status = OrderStatusEnum::COMPLETED;
            }
            $order->save();

            $order->load(['payment']);
            OrderHelper::confirmPayment($order);
            if($images){
                $order->payment->images = json_encode(array_filter((array)$images));
                $order->payment->save();
            }
            if($contentBanking){
                $order->payment->content_banking = $contentBanking;
                $order->payment->save();
            }
            return $this->httpResponse()->setError(false)->setMessage(__('Xác nhận thanh toán thành công'));
        }catch(\Exception $ex){
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($ex->getMessage());
        }
    }

}
