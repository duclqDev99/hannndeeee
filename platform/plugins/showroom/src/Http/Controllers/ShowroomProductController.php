<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Agent\Forms\AgentForm;
use Botble\Agent\Http\Requests\AgentRequest;
use Botble\Agent\Http\Requests\CreateAgentProductRequest;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentProduct;
use Botble\Agent\Tables\AgentProductTable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\SearchProductAndVariationsRequest;
use Botble\Ecommerce\Http\Resources\AvailableProductResource;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\Product;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Showroom\Http\Requests\CheckOnScanProductRequest;
use Botble\Showroom\Models\ShowroomOrder;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Showroom\Tables\ShowroomProductOddTable;
use Botble\Showroom\Tables\ShowroomProductTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShowroomProductController extends BaseController
{
    public function index(ShowroomProductOddTable $table)
    {
        PageTitle::setTitle(trans('Thành phẩm showroom'));

        Assets::addScripts([
            'bootstrap-editable',
        ])
            ->addStyles(['bootstrap-editable'])
            ->addScriptsDirectly([
                'vendor/core/plugins/showroom/js/showroom-detail-product.js',
            ]);

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Thêm sản phẩm'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/agent/js/agent-order-create.js',
            ])
            ->addScripts(['input-mask']);

        Assets::usingVueJS();

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }
        $listAgent = request()->user()->agent->pluck('id');

        $agencyProduct = AgentProduct::whereIn('agent_id', $listAgent)->get();

        return view('plugins/agent::products.create', compact('agencyProduct'));
    }

    public function store(CreateAgentProductRequest $request, BaseHttpResponse $response)
    {
        $agentUser = request()->user()->agent->first();
        $dataReq = $request->input();
        $dataProduct = $dataReq['products'];
        if (is_array($dataProduct) && count($dataProduct) > 0) {
            foreach ($dataProduct as $product) {
                if ($product['agent_product_id'] != null) {
                    AgentProduct::where('id', $product['agent_product_id'])
                        ->increment('quantity_not_qrcode', $product['select_qty']);
                    AgentProduct::where('id', $product['agent_product_id'])->update([
                        'agent_id' => $agentUser->id,
                    ]);
                } else {
                    AgentProduct::insert([
                        'agent_id' => $agentUser->id,
                        'product_id' => $product['product_id'],
                        'quantity_qrcode' => 0,
                        'quantity_not_qrcode' => $product['select_qty'],
                    ]);
                }
            }
        }
        return $response
            ->setPreviousUrl(route('agent-product.index'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Agent $agent, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $agent->name]));

        return $formBuilder->create(AgentForm::class, ['model' => $agent])->renderForm();
    }

    public function update(Agent $agent, AgentRequest $request, BaseHttpResponse $response)
    {
        $agent->fill($request->input());

        $agent->save();

        event(new UpdatedContentEvent(AGENT_MODULE_SCREEN_NAME, $request, $agent));

        return $response
            ->setPreviousUrl(route('agent.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Agent $agent, Request $request, BaseHttpResponse $response)
    {
        try {
            $agent->delete();

            event(new DeletedContentEvent(AGENT_MODULE_SCREEN_NAME, $request, $agent));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getAllProductAndVariations(
        SearchProductAndVariationsRequest $request,
        BaseHttpResponse                  $response
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

        $availableProducts = $availableProducts->simplePaginate(5);

        return $response->setData(AvailableProductResource::collection($availableProducts)->response()->getData());
    }

    public function getAllProductParent()
    {
        $products = Product::select('id', 'is_variation', 'name', 'sku', 'status', 'images')->where(['status' => BaseStatusEnum::PUBLISHED, 'is_variation' => 0])->get();
        return response()->json(['data' => $products], 200);
    }

    public function checkProductOrderInShowroom(CheckOnScanProductRequest $request, BaseHttpResponse $response)
    {
        $products = collect();

        $products = Product::query()
            ->wherePublished()
            ->where('id', $request->product_id)
            ->first();

        return $response
            ->setData(['product' => $products]);
    }

    public function getProductAndVariationsById(
        Request          $request,
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

        $availableProducts = $availableProducts->simplePaginate(5);

        return $response->setData(AvailableProductResource::collection($availableProducts)->response()->getData());
    }

    public function getProductInShowRoom(int|string $id, Request $request)
    {
        $keySearch = $request->keySearch;
        $select = [
            'id',
            'name',
            'images',
            'sku',
            'is_variation',
            'status'
        ];
        if ($keySearch != '') {
            $products = Product::select($select)->where(['status' => BaseStatusEnum::PUBLISHED])
                ->where(function ($q) use ($keySearch) {
                    return $q->where('name', 'LIKE', "%" . $keySearch . "%")->orWhere('sku', 'LIKE', "%" . $keySearch . "%");
                })
                ->with('productAttribute', 'parentProduct', 'productShowroom')->whereHas('productShowroom', function ($q) use ($id) {
                    $q->where('warehouse_id', $id);
                })->limit(10)->get();
        } else {

            $products = Product::select($select)->where(['status' => BaseStatusEnum::PUBLISHED])
                ->with('productAttribute', 'parentProduct', 'productShowroom')->whereHas('productShowroom', function ($q) use ($id) {
                    $q->where('warehouse_id', $id);
                })->limit(10)->get();
        }

        return response()->json([
            'data' => $products
        ]);
        $products = ShowroomProduct::where('warehouse_id', $id)->with('product.productAttribute')->get();
        return response()->json(['data' => $products], 200);
    }

    public function postConfirmReturnProduct(Request $request)
    {
        $result  = $this->httpResponse();
        DB::beginTransaction();
        try {
            $showroomOrder = ShowroomOrder::find($request->showroom_order_id);
            if (!$showroomOrder) throw new \Exception('Không tìm thấy đơn hàng!');

            $order = $showroomOrder->order;
            $order->update(['status' => OrderStatusEnum::RETURNED]);

            OrderHistory::query()->create([
                'action' => 'refund',
                'description' => Auth::user()->name . ' đã xác nhận hoàn hàng',
                'order_id' => $order->getKey(),
                'user_id' => Auth::id(),
            ]);

            $qrIds = $showroomOrder->list_id_product_qrcode;
            if (is_array($qrIds)) {
                foreach ($qrIds as $qrId) {
                    ProductQrcode::find($qrId)->update(['status' => QRStatusEnum::INSTOCK]);
                }
            }
            DB::commit();
            return $result->setMessage('Đã hoàn ' . count($qrIds) . ' sản phẩm');
        } catch (\Exception $e) {
            DB::rollBack();
            return $result->setError()->setMessage('Có lỗi sảy ra. Vui lòng thử lại sau');
        }
    }
    public function detail($id)
    {
        $products = Product::find($id);
        $productDetail = [
        ];
        foreach ($products->variations as $product) {
            $arrAttribute = $product->product->variationProductAttributes;
            list($color, $size, $stock, $total) = $this->extractColorAndSize($arrAttribute, $product->product->id);
            if ($stock) {
                $productDetail[] = [
                    'id' => $product->product->id,
                    'name' => $product->product->name,
                    'price' => $product->product->price,
                    'color' => $color,
                    'size' => $size,
                    'quantity' => $product->product->quantity,
                    'stock' => $stock,
                    'total' => $total
                ];
            }
        }
        return view('plugins/shared-module::product-detail.view-detail', compact('productDetail'));
    }
    private function extractColorAndSize($arrAttribute, $product_id)
    {
        $color = '';
        $size = '';
        $stock = [];
        $total = 0;
        $quantityInstocks = ShowroomProduct::where('product_id', $product_id)->get();
        foreach ($quantityInstocks as $quantityInstock) {
            if (!Auth::user()->hasPermission('showroom.all')) {
                $showroomList  = get_showroom_for_user();
                if ($showroomList->pluck('id')->contains($quantityInstock->warehouse->showroom->id)) {
                    $stock[] = [
                        'stock' => $quantityInstock->warehouse->name . ' - ' . $quantityInstock->warehouse->showroom->name,
                        'quantity' => $quantityInstock->quantity_qrcode
                    ];
                    $total += $quantityInstock->quantity_qrcode;
                }
            } else {
                $stock[] = [
                    'stock' => $quantityInstock->warehouse->name . ' - ' . $quantityInstock->warehouse->showroom->name,
                    'quantity' => $quantityInstock->quantity_qrcode
                ];
                $total += $quantityInstock->quantity_qrcode;
            }
        }
        foreach ($arrAttribute as $attribute) {
            if ($attribute?->color) {

                $color =  $attribute?->title;
            }
        }

        foreach ($arrAttribute as $attribute) {
            if (!$attribute?->color) {
                $size =  $attribute?->title;
            }
        }
        return [$color, $size, $stock, $total];
    }
}
