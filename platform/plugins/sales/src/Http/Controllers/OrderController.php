<?php

namespace Botble\Sales\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Forms\FormBuilder;
use Botble\Department\Models\OrderDepartment;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Http\Resources\AvailableProductResource;
use Botble\Ecommerce\Http\Resources\CartItemResource;
use Botble\Ecommerce\Models\Product;
use Botble\Sales\Models\Product as SaleProduct;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Ecommerce\Services\HandleTaxService;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Enums\OrderStepStatusEnum;
use Botble\Sales\Enums\TypeOrderEnum;
use Botble\Sales\Tables\OrderTable;
use Botble\Sales\Forms\CustomerForm;
use Botble\Sales\Http\Requests\CreateProductRequest;
use Botble\Sales\Http\Requests\CustomerRequest;
use Botble\Sales\Http\Resources\SampleProductResource;
use Botble\Sales\Models\Customer;
use Botble\Sales\Models\Order;
use Botble\Sales\Models\OrderDetail;
use Botble\Sales\Models\OrderHistory;
use Botble\Sales\Models\OrderReferenceProcedure;
use Botble\Sales\Models\Product as ProductOfSale;
use Botble\Sales\Models\Step;
use Botble\Sales\Models\StepInfo;
use Botble\Sales\Tables\CustomerTable;
use Botble\Sales\Supports\PurchaseOrderHelper;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
    public function __construct(
        protected HandleShippingFeeService $shippingFeeService,
        protected HandleApplyCouponService $handleApplyCouponService,
        protected HandleApplyPromotionsService $applyPromotionsService
    ) {

        $this
            ->breadcrumb()
            ->add(trans('plugins/sales::orders.menu'), route('purchase-order.index'));
    }

    public function index(OrderTable $table)
    {
        PageTitle::setTitle(trans('plugins/sales::sales.customer.name'));

        Assets::addScriptsDirectly([
            'vendor/core/plugins/sales/js/change-step.js',
        ]);

        return $table->renderTable();
    }

    public function create()
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/sales/js/order-create.js',
                'vendor/core/core/base/libraries/flatpickr/flatpickr.min.js'
            ])
            ->addScripts(['input-mask']);

        Assets::usingVueJS();

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        $this->pageTitle(trans('plugins/sales::orders.create'));

        return view('plugins/sales::orders.create');
    }

    public function store(Request $request)
    {
        $requestData = $request->input();

        try {
            if (isset($requestData['customer_id'])) { //Kiểm tra có tồn tại id của khách hàng không - Nếu có -> lấy thông tin của khách hàng đó
                $customer = Customer::where('id', $requestData['customer_id'])->first();
            }

            DB::beginTransaction();

            //Tạo mã đơn hàng
            $prefixCode = 'DH';
            $lastOrderId = !empty(Order::orderByDesc('id')->first()) ? Order::orderByDesc('id')->first()->id + 1 : 1;

            $COUNT_CODE = 7;

            $order_code = str_pad($lastOrderId, $COUNT_CODE, '0', STR_PAD_LEFT);

            // Sử dụng array_map và array_sum để tính tổng các phần tử trong mảng con
            $sums = array_column($requestData['products'], 'quantity');

            // Tính tổng của các tổng
            $totalQty = array_sum($sums);

            //Create total purchase orders
            $purchaseOrder = Order::query()->create([
                'order_code' => $prefixCode . $order_code,
                'type_order' => $requestData['type_order'],
                'link_order_id' => $requestData['type_order'] == TypeOrderEnum::SAMPLE ? null : $requestData['order_link_id'],
                'id_user' => $requestData['customer_id'],
                'username' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'invoice_issuer_name' => Auth::user()->name,
                'title' => $requestData['title'],
                'total_qty' => $totalQty,
                'amount' => $requestData['sub_amount'],
                'tax_amount' => $requestData['tax_amount'],
                'discount_amount' => $requestData['discount_amount'],
                'discount_description' => $requestData['discount_description'],
                'sub_total' => $requestData['amount'],
                'description' => isset($requestData['note']) ? $requestData['note'] : '',
                'expected_date' => $requestData['expected_date'],
                'option' => $requestData['option_order'],
                'created_by_id' => auth()->user()->id,
            ]);

            //Tạo các đơn chi tiết cho từng sản phẩm
            foreach ($requestData['products'] as $key => $item) {
                if ($item['is_sample'] === true) {
                    $product = SaleProduct::where('id', $item['id'])->first();
                    $type_model = SaleProduct::class;
                } else {
                    $product = Product::where('id', $item['id'])->first();
                    $type_model = Product::class;
                }
                OrderDetail::create([
                    'id_order' => $purchaseOrder->id,
                    'product_name' => $product->name,
                    'product_size' => '',
                    'product_type' => isset($product->product_type) ? $product->product_type : '',
                    'quantity' => $item['quantity'],
                    'product_id' => isset($item['id']) ? $item['id'] : '',
                    'type_model' => $type_model,
                ]);
            }

            //Lưu bước hiện tại của đơn
            // OrderReferenceProcedure::query()->create([
            //     'order_id' => $purchaseOrder->id,
            //     'procedure_code' => ProcedureOrder::where('cycle_point', 'start')->first()?->code,
            // ]);

            //Lưu trạng thái của bộ phận hiện tại
            // OrderDepartment::query()->create([
            //     'order_id' => $purchaseOrder->id,
            //     'department_code' => 'bp_sale',//Mã bộ phận hiện tại
            //     'assignee_id' => Auth::user()->id,
            //     'status' => 'completed',
            // ]);

            //Tạo thông báo cho admin
            // $arrNoti = [
            //     'action' => 'đặt sản phẩm cho khách hàng: ' . $customer->name,
            //     'permission' => false,
            //     'route' => route('purchase-order.edit', $purchaseOrder),
            //     'status' => 'Chờ duyệt'
            // ];
            // send_notify_cms_and_tele($purchaseOrder, $arrNoti);

         

            DB::commit();

            return $this
                ->httpResponse()
                ->setData($purchaseOrder->load('steps.departments'))
                ->withCreatedSuccessMessage();
        } catch (Exception $err) {
            DB::rollBack();
            return $err;
        }
    }

    public function edit(Order $order, FormBuilder $formBuilder)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
            ])
            ->addScripts(['input-mask']);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        $order->load(['order_detail']);

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $order->order_code]));

        return view('plugins/sales::orders.edit', compact('order'));
    }

    public function update(Order $order, Request $request)
    {
        $order->fill($request->input());

        $order->save();

        event(new UpdatedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $order));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('purchase-order.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(Order $order, Request $request, BaseHttpResponse $response)
    {
        try {
            $order->delete();

            if ($order->order_detail) {
                foreach ($order->order_detail as $item) {
                    $item->delete();
                }
            }

            event(new DeletedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $order));

            return $response->setMessage('Xoá đối tượng thành công!!');
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function prevStep(Request $request, BaseHttpResponse $response)
    {
        try {
            $order = Order::findOrFail($request->order_id);
            $currentProcedure = $order->current_procedure;
            $nextStep = json_decode($currentProcedure->next_step);
            $order->update(['procedure_code' => $nextStep->prev]);

            return response()->json([
                'message' => 'successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function nextStep(Request $request)
    {
        try {
            $order = Order::findOrFail($request->order_id);
            $currentProcedure = $order->current_procedure;
            $nextStep = json_decode($currentProcedure->next_step);
            $order->update(['procedure_code' => $nextStep->next]);

            return response()->json([
                'message' => 'successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    //Kiểm tra dữ liệu trước khi tạo order
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

        $products = [];

        foreach ($request->input('products') as $item) {
            if ($item['is_sample'] === false) {
                $product = Product::query()
                    ->where('id', $item['id'])
                    ->with($with)
                    ->first();

                if (!empty($product)) {
                    array_push($products, $product);
                }
            } else {
                $product = SaleProduct::query()
                    ->where('id', $item['id'])
                    ->first();

                if (!empty($product)) {
                    array_push($products, $product);
                }
            }
        }

        if (!$products) {
            $products = collect();
        } else {
            $products = collect($products);
        }

        $weight = 0;
        $discountAmount = 0;
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
            $productOptions = [];

            if ($inputProduct['is_sample'] === false) {
                $productName = $product->original_product->name ?: $product->name;

                $cartItemsById = $cartItems->where('id', $productId);
                $inputQty = Arr::get($inputProduct, 'quantity') ?: 1;
                $qty = $inputQty;
                $qtySelected = 0;
                if ($cartItemsById->count()) {
                    $qtySelected = $cartItemsById->sum('qty');
                }

                if ($product->isOutOfStock()) {
                    $isError = true;
                    $message[] = __('Product :product is out of stock!', ['product' => $productName]);
                }

                if ($inputOptions = Arr::get($inputProduct, 'options') ?: []) {
                    $productOptions = OrderHelper::getProductOptionData($inputOptions);
                }
                $originalQuantity = $product->quantity ?: 0;
                $product->quantity = (int)$product->quantity - $qtySelected - $inputQty + 1;

                if ($product->quantity < 0) {
                    $product->quantity = 0;
                }

                $product->quantity = $originalQuantity;

                if ($product->original_product->options->where('required', true)->count()) {
                    if (!$inputOptions) {
                        $isError = true;
                        $message[] = __('Vui lòng chọn tùy chọn sản phẩm!');
                    } else {
                        $requiredOptions = $product->original_product->options->where('required', true);

                        foreach ($requiredOptions as $requiredOption) {
                            if (!Arr::get($inputOptions, $requiredOption->id . '.values')) {
                                $isError = true;
                                $message[] = trans(
                                    'plugins/ecommerce::product-option.add_to_cart_value_required',
                                    ['value' => $requiredOption->name]
                                );
                            }
                        }
                    }
                }

                $parentProduct = $product->original_product;

                $image = $product->image ?: $parentProduct->image;
                $taxRate = app(HandleTaxService::class)->taxRate($parentProduct, $country, $state, $city, $zipCode);
            } else {
                $productName =  $product->name;

                $cartItemsById = $cartItems->where('id', $productId);
                $inputQty = Arr::get($inputProduct, 'quantity') ?: 1;
                $qty = $inputQty;
                $qtySelected = 0;
                if ($cartItemsById->count()) {
                    $qtySelected = $cartItemsById->sum('qty');
                }

                $image = null;
                $taxRate = 0;
            }

            $options = [
                'name' => $productName,
                'image' => $image ?: null,
                'attributes' => $product->is_variation ? $product->variation_attributes : '',
                'taxRate' => $taxRate ?: null,
                'options' => $productOptions ?: [],
                'extras' => [],
                'sku' => $product->sku,
                'weight' => isset($product->original_product->weight) ? $product->original_product->weight : 0,
                'original_price' => isset($product->original_price) ? $product->original_price : $product->price,
                'product_link' => isset($product->original_product->id) ? route('products.edit', $product->original_product->id) : route('product-sample.view', $product->id),
                'product_type' => isset($product->product_type) ? (string)$product->product_type : null,
                'is_sample' => $inputProduct['is_sample']
            ];

            $price = isset($product->original_price) ? $product->original_price : $product->price;
            $price = Cart::getPriceByOptions($price, $productOptions);

            $cartItem = CartItem::fromAttributes(
                $product->id,
                BaseHelper::clean(isset($parentProduct->name) ? $parentProduct->name : $product->name),
                $price,
                $options
            );

            $cartItemExists = $cartItems->firstWhere('rowId', $cartItem->rowId);

            if (!$cartItemExists) {
                $cartItem->setQuantity($qty);
                $cartItem->setTaxRate($taxRate);

                $cartItems[] = $cartItem;

                if ($inputProduct['is_sample'] === false) {
                    if (!$product->isTypeDigital()) {
                        $weight += $product->original_product->weight * $qty;
                    }
                    $product->cartItem = $cartItem;
                }
                $productItems[] = $product;
            }
        }

        if (isset($request->input()['discount_amount'])) {
            $discountAmount = $request->input()['discount_amount'];
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

        $weight = EcommerceHelper::validateOrderWeight($weight);

        if ($couponCode = trim($request->input('coupon_code'))) {
            $couponData = $this->handleApplyCouponService->applyCouponWhenCreatingOrderFromAdmin($request, $cartData);
            if (Arr::get($couponData, 'error')) {
                $isError = true;
                $message[] = Arr::get($couponData, 'message');
            } else {
                $discountAmount = Arr::get($couponData, 'data.discount_amount');
                if (!$discountAmount) {
                    $isError = true;
                    $message[] = __('Coupon code is not valid or does not apply to the products');
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

        $totalAmount = max($rawTotal - $discountAmount, 0);

        $data = [
            'customer_id' => $customerId,
            'products' => CartItemResource::collection($cartItems),
            'weight' => $weight,
            'discount_amount' => $discountAmount,
            'discount_amount_label' => format_price($discountAmount),
            'sub_amount' => $subAmount,
            'sub_amount_label' => format_price($subAmount),
            'tax_amount' => $taxAmount,
            'tax_amount_label' => format_price($taxAmount),
            'total_amount' => $totalAmount,
            'total_amount_label' => format_price($totalAmount),
            'coupon_data' => $couponData,
            'coupon_code' => $couponCode,
            'update_context_data' => true,
            'error' => $isError,
            'message' => $message,
        ];

        if (is_plugin_active('marketplace')) {
            $data['store'] = $stores->first() ?: [];
        }

        return $data;
    }

    //Reorder
    public function getReorder(Request $request)
    {
        if (!$request->input('order_id')) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(route('orders.index'))
                ->setMessage(trans('plugins/ecommerce::order.order_is_not_existed'));
        }

        $this->pageTitle(trans('plugins/sales::order.reorder'));

        Assets::usingVueJS();

        $order = Order::query()->find($request->input('order_id'));

        if (!$order) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(route('orders.index'))
                ->setMessage(trans('plugins/ecommerce::order.order_is_not_existed'));
        }

        $productIds = $order->order_detail->pluck('product_id')->all();

        $products = [];

        foreach ($order->order_detail as $item) {
            if ($item->type_model === Product::class) {
                $product = Product::query()
                    ->where('id', $item->product_id)
                    ->first();

                if (!empty($product)) {
                    array_push($products, $product);
                }
            } else {
                $product = SaleProduct::query()
                    ->where('id', $item->product_id)
                    ->first();

                if (!empty($product)) {
                    array_push($products, $product);
                }
            }
        }

        if (!$products) {
            $products = collect();
        } else {
            $products = collect($products);
        }

        $cartItems = collect();
        foreach ($order->order_detail as $orderProduct) {
            $product = $products->firstWhere('id', $orderProduct->product->id);

            if (!$product) {
                continue;
            }

            $is_sample = true;
            $options = [];

            if ($orderProduct->type_model === Product::class) {
                $is_sample = false;
                $options = $orderProduct->product->options;
            }

            $options = [
                'options' => $options,
                'is_sample' => $is_sample
            ];

            $cartItem = CartItem::fromAttributes($product->id, $orderProduct->product->name, 0, $options);
            $cartItem->setQuantity($orderProduct ? $orderProduct->quantity : 1);

            $cartItems[] = $cartItem;
        }
        $products = CartItemResource::collection($cartItems);

        $customer = $order->customer;

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/sales/js/order-create.js',
            ])
            ->addScripts(['input-mask']);

        return view(
            'plugins/sales::orders.reorder',
            compact(
                'order',
                'products',
                'productIds',
                'customer',
            )
        );
    }

    //Tạo sản phẩm mới khi tạo đơn đặt hàng
    public function postCreateProductWhenCreatingOrder(
        CreateProductRequest $request,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $requestData = $request->input();

        $dataInsert = [
            "name" => $requestData['name'],
            "unit" => $requestData['unit'],
            "price" => $requestData['price'],
            "sku" => $requestData['sku'],
            "color" => $requestData['color'],
            "size" => $requestData['size'],
            "ingredient" => $requestData['ingredient'],
            "description" => $requestData['description'],
            "image" => '',
        ];
        $product = ProductOfSale::query()->create($dataInsert);

        event(new CreatedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $product));

        return $response
            ->setData(new SampleProductResource($product))
            ->withCreatedSuccessMessage();
    }

    //Lấy toàn bộ danh sách sản phẩm thương mại và sản phẩm mẫu
    public function getAllProductAndVariations(
        Request $request,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $selectedProducts = collect();

        if ($productIds = $request->input('product_ids', [])) {
            $selectedProducts = Product::query()
                ->wherePublished()
                ->whereIn('id', $productIds)
                ->with(['variationInfo.configurableProduct'])
                ->get();
        }

        $keyword = $request->input('keyword');

        $availableProducts = Product::query()
            ->select(['ec_products.*'])
            ->where('is_variation', false)
            ->wherePublished()
            ->with(['variationInfo.configurableProduct'])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query
                        ->where('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sku', 'LIKE', '%' . $keyword . '%');
                });
            });

        if (is_plugin_active('marketplace') && $selectedProducts->count()) {
            $selectedProducts = $selectedProducts->map(function ($item) {
                if ($item->is_variation) {
                    $item->store_id = $item->original_product->store_id;
                }

                if (!$item->store_id) {
                    $item->store_id = 0;
                }

                return $item;
            });
            $storeIds = array_unique($selectedProducts->pluck('store_id')->all());
            $availableProducts = $availableProducts->whereIn('store_id', $storeIds)->with(['store']);
        }

        $sampleProduct = ProductOfSale::query()
            ->select(['hd_products.*'])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query
                        ->where('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sku', 'LIKE', '%' . $keyword . '%');
                });
            });

        // Merge sampleProduct
        $availableProducts = $availableProducts->simplePaginate(10);
        $sampleProduct = $sampleProduct->simplePaginate(10);

        $dataAvailableProduct = AvailableProductResource::collection($availableProducts)->response()->getData();
        $dataSampleProduct = SampleProductResource::collection($sampleProduct)->response()->getData();

        $firstData = collect($dataAvailableProduct->data);
        $secondData = collect($dataSampleProduct->data);

        // Gộp hai đối tượng Collection lại với nhau dựa trên trường 'id'
        $mergedCollection = $secondData->keyBy('id')->merge($firstData->keyBy('id'));

        // Chuyển đối tượng Collection thành mảng
        $mergedArray = $mergedCollection->values()->all();
        $dataAvailableProduct->data = $mergedArray;

        return $response->setData($dataAvailableProduct);
    }

    ////////////////////////////////////////////////////////////////
    /**---------------- Chỉnh sửa đơn đặt hàng ------------------ */
    ////////////////////////////////////////////////////////////////
    public function editOrderCustomer(Order $order, Request $request)
    {
        if (empty($order)) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Không tìm thấy đơn đặt hàng này!!');
        }

        //Không cho phép chỉnh sửa nếu trạng thái của đơn khác trạng thái Pending: tạo mới - chờ duyệt
        if ($order->status == OrderStatusEnum::COMPLETED) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Đơn hàng này hiện không thể chỉnh sửa!!');
        }

        $requestData = $request->input();

        try {
            if (isset($requestData['customer_id'])) { //Kiểm tra có tồn tại id của khách hàng không - Nếu có -> lấy thông tin của khách hàng đó
                $customer = Customer::where('id', $requestData['customer_id'])->first();
            }

            //Tạo mã đơn hàng
            // Sử dụng array_map và array_sum để tính tổng các phần tử trong mảng con
            $sums = array_column($requestData['products'], 'quantity');
            // Tính tổng của các tổng
            $totalQty = array_sum($sums);

            DB::beginTransaction();

            $dataUpdate = [
                'id_user' => $customer->id,
                'type_order' => $requestData['type_order'],
                'link_order_id' => $requestData['type_order'] == TypeOrderEnum::SAMPLE ? null : $requestData['order_link_id'],
                'username' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'title' => $requestData['title'],
                'total_qty' => $totalQty,
                'amount' => $requestData['sub_amount'],
                'tax_amount' => $requestData['tax_amount'],
                'discount_amount' => $requestData['discount_amount'],
                'discount_description' => $requestData['discount_description'],
                'sub_total' => $requestData['amount'],
                'description' => $requestData['note'],
                'expected_date' => $requestData['expected_date'],
                'status' => OrderStatusEnum::PENDING,
                'option' => $requestData['option_order']
            ];

            //Update purchase orders
            $order->update($dataUpdate);
            event(new UpdatedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $order));

            //Xoá các chi tiết trong đơn cũ
            foreach ($order->order_detail as $key => $item) {
                $item->delete();
            }
            //Tạo các đơn chi tiết cho từng sản phẩm
            foreach ($requestData['products'] as $key => $item) {
                if ($item['is_sample'] === true) {
                    $product = SaleProduct::where('id', $item['id'])->first();
                    $type_model = SaleProduct::class;
                } else {
                    $product = Product::where('id', $item['id'])->first();
                    $type_model = Product::class;
                }
                OrderDetail::create([
                    'id_order' => $order->id,
                    'product_name' => $product->name,
                    'product_size' => '',
                    'product_type' => isset($product->product_type) ? $product->product_type : '',
                    'quantity' => $item['quantity'],
                    'product_id' => isset($item['id']) ? $item['id'] : '',
                    'type_model' => $type_model,
                ]);
            }

            ////////////////////////////////////////////////////////////////
            /**-------------- Lưu lại dữ liệu thực thi------------------- */
            ////////////////////////////////////////////////////////////////
            DB::commit();

            return $this
                ->httpResponse()
                ->setData($order)
                ->withCreatedSuccessMessage();
        } catch (Exception $err) {
            DB::rollBack();
            return $err;
        }
    }

    ////////////////////////////////////////////////////////////////
    /**-------------- In phiếu đề xuất đặt hàng ----------------- */
    ////////////////////////////////////////////////////////////////
    public function getGenerateInvoice(Order $order, Request $request, PurchaseOrderHelper $receiptHelper)
    {
        if ($request->input('type') == 'print') {
            return $receiptHelper->streamInvoice($order);
        }

        return $receiptHelper->downloadInvoice($order);
    }

    public function postConfirm(Order $order, Request $request)
    {
        abort_if(empty($order), 403);

        if ($order->status != OrderStatusEnum::PENDING) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Đơn này đã được tiến hành hoặc bị huỷ. Không thể duyệt được!!');
        }

        $arrStatusProcess = [];
        $arrNameProcess = [];

        $depart_code_current = 'bp_sale'; //Mã của bộ phận hiện tại
        $getProcedure = ProcedureOrder::where('department_code', $depart_code_current)->first();

        //Lấy trạng thái của bộ phận hiện tại
        $statusCurrentDepartment = OrderDepartment::where(['order_id' => $order->id, 'department_code' => $depart_code_current])->first()?->status;

        if (!empty($order->procedures)) {
            // Kiểm tra nếu giải mã thành công
            foreach ($order->procedures as $key => $item) { //Lấy từng giá trị trong mảng các bước tiếp theo
                if ($item->code == $getProcedure->code) {
                    foreach ($getProcedure->next_step as $keyStep => $subArray) { //Lấy từng điều kiện trong quy trình
                        //Kiểm tra key có tồn tại trong mảng hay không

                        if (array_key_exists($getProcedure->code, $subArray)) {
                        }
                        if ($statusCurrentDepartment == $subArray[$getProcedure->code]) {
                            $procedureNext = ProcedureOrder::where('code', $keyStep)->first();

                            if (!empty($procedureNext)) {
                                $arrStatusProcess[$procedureNext->code] = 'waiting';
                                $arrNameProcess[$procedureNext->code] = $procedureNext->name;
                            }
                        }
                    }
                }
            }
        }

        $order->status = OrderStatusEnum::PROCESSING;
        $order->save();
        event(new UpdatedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $order));

        //Tạo trạng thái tiếp nhận đơn cho các bộ phận tiếp theo
        foreach ($arrStatusProcess as $key => $value) {
            OrderDepartment::create([
                'order_id' => $order->id,
                'department_code' => $key,
                'status' => $value,
            ]);
        }

        //Lưu lại lịch sử đơn hàng
        foreach ($arrStatusProcess as $key => $item) {
            # code...
            $orderHistory = OrderHistory::query()->create([
                'order_id' => $order->id,
                'procedure_code_previous' => $getProcedure->code,
                'procedure_name_previous' => $getProcedure->name,
                'procedure_code_current' => $key,
                'procedure_name_current' => $arrNameProcess[$key],
                'created_by' => Auth::user()->id,
                'created_by_name' => Auth::user()->name,
                'status' => 'waiting',
            ]);
            event(new CreatedContentEvent(HISTORY_ORDER_MODULE_SCREEN_NAME, $request, $order));
        }

        //Import

        return $this
            ->httpResponse()
            ->withCreatedSuccessMessage();
    }

    public function cancelPurchaseOrder(Order $order, Request $request)
    {
        abort_if(empty($order), 403);

        if ($order->status != OrderStatusEnum::PENDING) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Đơn này đã được tiến hành hoặc bị huỷ. Không thể huỷ được!!');
        }

        $order->status = OrderStatusEnum::CANCELED;
        $order->save();

        event(new UpdatedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $order));

        return $this
            ->httpResponse()
            ->withCreatedSuccessMessage();
    }

    public function getListPurchaseOrder()
    {
        $orders = Order::query()->get();

        return $this
            ->httpResponse()->setData($orders);
    }
}
