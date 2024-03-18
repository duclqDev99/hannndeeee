<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Http\Requests\WarehouseRequest;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Base\Facades\PageTitle;
use Botble\HubWarehouse\Tables\DetailBatchTable;
use Botble\HubWarehouse\Tables\DetailProductTable;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Illuminate\Http\Request;
use Exception;
use Botble\HubWarehouse\Tables\WarehouseTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\HubWarehouse\Forms\WarehouseForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\HubWarehouse\Http\Requests\CreateProductOddRequest;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Tables\ProductOddTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Kho hàng của hub'), route('hub-warehouse.index'));
    }
    public function index(WarehouseTable $table)
    {
        PageTitle::setTitle(trans('plugins/hub-warehouse::warehouse.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/hub-warehouse::warehouse.create'));

        return $formBuilder->create(WarehouseForm::class)->renderForm();
    }

    public function store(WarehouseRequest $request, BaseHttpResponse $response)
    {
        $hubStock = Warehouse::query()->create($request->input());

        event(new CreatedContentEvent(WAREHOUSE_MODULE_SCREEN_NAME, $request, $hubStock));

        return $response
            ->setPreviousUrl(route('hub-stock.index'))
            ->setNextUrl(route('hub-stock.edit', $hubStock->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Warehouse $hubStock, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $hubStock->name]));
        $hubStock = $this->fillterWarehouseByAgent($hubStock);
        return $formBuilder->create(WarehouseForm::class, ['model' => $hubStock])->renderForm();
    }

    public function update(Warehouse $hubStock, WarehouseRequest $request, BaseHttpResponse $response)
    {
        $hubStock->fill($request->input());

        $hubStock->save();

        event(new UpdatedContentEvent(WAREHOUSE_MODULE_SCREEN_NAME, $request, $hubStock));

        return $response
            ->setPreviousUrl(route('hub-stock.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Warehouse $hubStock, Request $request, BaseHttpResponse $response)
    {
        try {
            $hubStock->delete();

            event(new DeletedContentEvent(WAREHOUSE_MODULE_SCREEN_NAME, $request, $hubStock));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function getWarehouseByHub(int|string $id)
    {
        $warehouse = Warehouse::where('hub_id', $id)->get();
        return response()->json($warehouse, 200);
    }
    private function fillterWarehouseByAgent($query)
    {
        if (!request()->user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            $hubUsers = HubUser::where('user_id', \Auth::id())->get();
            $hubIds = $hubUsers->pluck('hub_id')->toArray();
            if (!in_array($query->hub_id, $hubIds)) {
                abort(403, 'Không có quyền truy cập kho này');
            }
        }
        return $query;
    }

    public function detailOddInStock(Warehouse $stock, Request $request, ProductOddTable $table)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/hub-warehouse/js/agent-warehouse.js',
        ]);
        $this->pageTitle(trans('sản phẩm lẻ trong kho / ' . $stock->name));
        $request->merge(['id' => $stock->id]);

        return $table->renderTable();
    }

    public function reduceQuantity(Request $request, BaseHttpResponse $response)
    {
        $quantity = $request->input()['quantity'];
        $id = $request->input()['id'];

        $productInStock = QuantityProductInStock::where('id', $id)->first();

        if ($productInStock) {
            $productInStock->decrement('quantity_not_qrcode', $quantity);
            $productInStock->increment('quantity_sold_not_qrcode', $quantity);
        }

        // QuantityProductInStock::where('id', $request->input()['id'])
        //     ->decrement('quantity_not_qrcode', $request->input()['quantity']);

        return $response
            ->setMessage(trans('Chỉnh sửa số lượng thành công'));
    }

    public function createManual(Warehouse $agent, Request $request, $id, BaseHttpResponse $response)
    {
        PageTitle::setTitle(trans('Thêm sản phẩm thủ công'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/hub-warehouse/js/add-agent-user.js',
            ])
            ->addScripts(['input-mask']);

        Assets::usingVueJS();

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }
        $listAgent = request()->user()->agent->pluck('id');

        $agencyProduct = QuantityProductInStock::where('stock_id', $id)->get();

        $warehouse = Warehouse::where('id', $id)->first();

        return view('plugins/hub-warehouse::product-odd-qrcode.create', compact('agencyProduct', 'warehouse'));
    }

    public function storeManual(CreateProductOddRequest $request, BaseHttpResponse $response)
    {
        $dataReq = $request->input();
        $dataProduct = $dataReq['products'];
        $today = Carbon::today();
        DB::beginTransaction();
        try {

            if (is_array($dataProduct) && count($dataProduct) > 0) {
                foreach ($dataProduct as $product) {
                    if ($product['agent_product_id'] != null) {
                        QuantityProductInStock::where('id', $product['agent_product_id'])
                            ->increment('quantity_not_qrcode', $product['select_qty']);
                        // AgentProduct::where('id', $product['agent_product_id'])->update([
                        //     'warehouse_id' => $product['warehouse_id'],
                        // ]);
                    } else {
                        QuantityProductInStock::insert([
                            'stock_id' => $product['warehouse_id'],
                            'product_id' => $product['product_id'],
                            'quantity' => 0,
                            'quantity_not_qrcode' => $product['select_qty'],
                            'created_at' => $today,
                        ]);
                    }
                }
            }
            DB::commit();
            return $response
                ->setPreviousUrl(route('agent-product.index'))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME:' + $e);
        }
    }
    public function checkQuantityBatch($id, Request $request)
    {
        $products = [];

        $productsData = $request->input('productsData', []);

        foreach ($productsData as $product) {
            $quantityInStock = ProductBatch::where([
                'warehouse_id' => $id,
                'warehouse_type' => Warehouse::class,
                'product_parent_id' => $product['product_id']
            ])->where('status', ProductBatchStatusEnum::INSTOCK)->count();

            $prd = Product::find($product['product_id']);

            if ($product['quantity'] > $quantityInStock) {
                $products[] = [
                    'productId' => $product['product_id'],
                    'product' => $prd,
                    'quantityInStock' => $quantityInStock,
                    'quantity' => $product['quantity']
                ];
            }
        }

        return response()->json(['data' => $products], 200);
    }
    public function detailBatchInStock(Warehouse $stock, DetailBatchTable $table, Request $request)
    {
        $request->merge(['id' => $stock->id]);
        return $table->renderTable();
    }
    public function detailProductInStock(Warehouse $stock, DetailProductTable $table, Request $request)
    {
        PageTitle::setTitle(trans('Chi tiết sản phẩm lẻ :name', ['name' => $stock->name]));
        $request->merge(['id' => $stock->id]);
        return $table->renderTable();
    }
    public function getAllWarehouse()
    {
        $warehouseHub = Warehouse::where('status', HubStatusEnum::ACTIVE)->get();
        return response()->json(['data' => $warehouseHub, 'success' => 1], 200);
    }


}
