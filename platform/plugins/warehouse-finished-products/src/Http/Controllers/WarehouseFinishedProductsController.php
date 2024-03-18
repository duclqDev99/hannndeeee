<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\WarehouseFinishedProducts\Http\Requests\CreateProductOddRequest;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\WarehouseFinishedProducts\Http\Requests\WarehouseFinishedProductsRequest;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Illuminate\Http\Request;
use Exception;
use Botble\WarehouseFinishedProducts\Tables\WarehouseFinishedProductsTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\WarehouseFinishedProducts\Forms\WarehouseFinishedProductsForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Botble\WarehouseFinishedProducts\Tables\ProductBatchTable;
use Botble\WarehouseFinishedProducts\Tables\WarehouseFinishedProductOddTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseFinishedProductsController extends BaseController
{
    public function index(WarehouseFinishedProductsTable $table)
    {
        PageTitle::setTitle(trans('Danh sách kho thành phẩm'));
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Tạo kho thành phẩm'));

        return $formBuilder->create(WarehouseFinishedProductsForm::class)->renderForm();
    }

    public function store(WarehouseFinishedProductsRequest $request, BaseHttpResponse $response)
    {
        $warehouseFinishedProducts = WarehouseFinishedProducts::query()->create($request->input());

        event(new CreatedContentEvent(WAREHOUSE_FINISHED_PRODUCTS_MODULE_SCREEN_NAME, $request, $warehouseFinishedProducts));

        return $response
            ->setPreviousUrl(route('warehouse-finished-products.index'))
            ->setNextUrl(route('warehouse-finished-products.edit', $warehouseFinishedProducts->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }
    public function edit(WarehouseFinishedProducts $warehouseFinishedProducts, FormBuilder $formBuilder)
    {
        if (!request()->user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            abort_if(!in_array($warehouseFinishedProducts->id, request()->user()->warehouse_finished()->pluck('warehouse_id')->toArray()), 403, 'Không có quyền chỉnh sửa kho thành phẩm này');
        }
        PageTitle::setTitle(trans('Chỉnh sửa kho thành phẩm :name', ['name' => $warehouseFinishedProducts->name]));

        return $formBuilder->create(WarehouseFinishedProductsForm::class, ['model' => $warehouseFinishedProducts])->renderForm();
    }

    public function update(WarehouseFinishedProducts $warehouseFinishedProducts, WarehouseFinishedProductsRequest $request, BaseHttpResponse $response)
    {
        $warehouseFinishedProducts->fill($request->input());

        $warehouseFinishedProducts->save();

        event(new UpdatedContentEvent(WAREHOUSE_FINISHED_PRODUCTS_MODULE_SCREEN_NAME, $request, $warehouseFinishedProducts));

        return $response
            ->setPreviousUrl(route('warehouse-finished-products.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(WarehouseFinishedProducts $warehouseFinishedProducts, Request $request, BaseHttpResponse $response)
    {
        try {
            $warehouseFinishedProducts->delete();

            event(new DeletedContentEvent(WAREHOUSE_FINISHED_PRODUCTS_MODULE_SCREEN_NAME, $request, $warehouseFinishedProducts));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function detailBatchInStock(WarehouseFinishedProducts $stock, Request $request, ProductBatchTable $table)
    {
        PageTitle::setTitle('Chi tiết lô hàng có trong kho - '. $stock->name);
        $request->merge(['id' => $stock->id]);

        return $table->renderTable();
    }
    public function getAllWarehouse()
    {
        $warehouse = WarehouseFinishedProducts::where('status', BaseStatusEnum::PUBLISHED)->get();
        return response()->json(['data' => $warehouse], 200);
    }

    public function detailOddInStock(WarehouseFinishedProducts $stock,Request $request, WarehouseFinishedProductOddTable $table)
    {
        // QuantityProductInStock $stock
        Assets::addScriptsDirectly([
            'vendor/core/plugins/warehouse-finished-products/js/agent-warehouse.js',
        ]);
        PageTitle::setTitle(trans('Chi tiết sản phẩm lẻ có trong kho - ' . $stock->name));
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
        // QuantityProductInStock::where('id', $request->input()['id'])
        //     ->increment('quantity_sold_not_qrcode', $request->input()['quantity']);
        return $response
            ->setMessage(trans('Chỉnh sửa số lượng thành công'));
        // $request->merge(['id' => $stock->id]);

        // return $table->renderTable();
    }

    public function createManual(WarehouseFinishedProducts $agent, Request $request, $id, BaseHttpResponse $response)
    {
        PageTitle::setTitle(trans('Thêm sản phẩm thủ công'));

        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
        ->addScriptsDirectly([
            'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
            'vendor/core/plugins/warehouse-finished-products/js/list-product-stock.js',
        ])
        ->addScripts(['input-mask']);

        Assets::usingVueJS();

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }
        $listAgent = request()->user()->agent->pluck('id');

        $agencyProduct = QuantityProductInStock::where('stock_id', $id)->get();

        $warehouseFinishedProducts = WarehouseFinishedProducts::where('id', $id)->first();

        return view('plugins/warehouse-finished-products::products.create',compact('agencyProduct','warehouseFinishedProducts'));
    }

    public function storeManual(CreateProductOddRequest $request, BaseHttpResponse $response)
    {
        $dataReq = $request->input();
        $dataProduct = $dataReq['products'];
        $today = Carbon::today();
        DB::beginTransaction();
        try{

            if(is_array($dataProduct) && count($dataProduct) > 0){
                foreach($dataProduct as $product){
                    if($product['agent_product_id'] != null){
                        QuantityProductInStock::where('id', $product['agent_product_id'])
                            ->increment('quantity_not_qrcode', $product['select_qty']);
                        // AgentProduct::where('id', $product['agent_product_id'])->update([
                        //     'warehouse_id' => $product['warehouse_id'],
                        // ]);
                    }else{
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
                ->setMessage(trans('core/base::notices.create_success_message'));
        }catch(Exception  $e){
            DB::rollBack();
            throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME:' + $e);
        }
    }
}
