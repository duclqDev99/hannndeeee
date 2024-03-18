<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Http\Requests\AgentRequest;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentUser;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Agent\Tables\AgentProductTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\AgentForm;
use Botble\Agent\Http\Requests\CreateAgentProductRequest;
use Botble\Agent\Models\AgentProduct;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\SearchProductAndVariationsRequest;
use Botble\Ecommerce\Http\Resources\AvailableProductResource;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Facades\Auth;

class AgentProductController extends BaseController
{
    public function index(AgentProductTable $table)
    {
        PageTitle::setTitle(trans('plugins/agent::agent.name'));

        Assets::addScripts([
            'bootstrap-editable',
        ])
            ->addStyles(['bootstrap-editable'])
            ->addScriptsDirectly([
                'vendor/core/plugins/agent/js/agent-product.js',
            ]);

        return $table->renderTable();
    }
    public function detail($id)
    {
        $products = Product::find($id);
        $productDetail = [
        ];
        foreach ($products->variations as $product) {
            $arrAttribute = $product->variationItems;
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
        $quantityInstocks = AgentProduct::where('product_id', $product_id)->get();
        foreach ($quantityInstocks as $quantityInstock) {
            if (!Auth::user()->hasPermission('hub-warehouse.all-permissions')) {
                $agentList = request()->user()->agent->pluck('id');
                if (in_array($quantityInstock->warehouse->id, $agentList)) {
                    $stock[] = [
                        'stock' => $quantityInstock->warehouse->name . ' - ' . $quantityInstock->warehouse->agent->name,
                        'quantity' => $quantityInstock->quantity_qrcode
                    ];
                    $total += $quantityInstock->quantity_qrcode;
                }
            } else {
                $stock[] = [
                    'stock' => $quantityInstock->warehouse->name . ' - ' . $quantityInstock->warehouse->agent->name,
                    'quantity' => $quantityInstock->quantity_qrcode
                ];
                $total += $quantityInstock->quantity_qrcode;
            }
        }
        if (count($arrAttribute) === 2) {
            $size = ($arrAttribute[1]->attribute->title);
            $color = ($arrAttribute[0]->attribute->title);
        }
        return [$color, $size, $stock, $total];
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
    public function getAllProductParent()
    {
        $products = Product::select('id', 'is_variation', 'name', 'sku', 'status', 'images')->where(['status' => BaseStatusEnum::PUBLISHED, 'is_variation' => 0])->get();
        return response()->json(['data' => $products], 200);
    }
    public function getProductInAgent(int|string $id)
    {
        $select = [
            'id',
            'name',
            'images',
            'sku',
            'is_variation',
            'status'
        ];
        $products = Product::select($select)->where(['status' => BaseStatusEnum::PUBLISHED])
            ->with('productAttribute', 'parentProduct','productAgent')->whereHas('productAgent', function ($q) use ($id) {
                $q->where('warehouse_id', $id);
            })->get();
        return response()->json([
            'data' => $products
        ]);

        $products = AgentProduct::where('warehouse_id', $id)->where('quantity_qrcode', '>', 0)->with([
            'product.variationInfo',
            'product.variationProductAttributes',
            'product.parentProduct'
        ])
            ->get();
        return response()->json(['data' => $products], 200);
    }
}
