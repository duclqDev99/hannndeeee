<?php

namespace Botble\HubWarehouse\Http\Controllers;

use Botble\HubWarehouse\Http\Requests\HubWarehouseRequest;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\Base\Facades\PageTitle;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\SaleWarehouse\Models\SaleProduct;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\ProductBatchDetail;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Illuminate\Http\Request;
use Exception;
use Botble\HubWarehouse\Tables\HubWarehouseTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\HubWarehouse\Forms\HubWarehouseForm;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\DB;

class HubWarehouseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Danh sách HUB'), route('hub-warehouse.index'));
    }
    public function index(HubWarehouseTable $table)
    {

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('Thêm mới'));

        return $formBuilder->create(HubWarehouseForm::class)->renderForm();
    }

    public function store(HubWarehouseRequest $request, BaseHttpResponse $response)
    {
        $hubWarehouse = HubWarehouse::query()->create($request->input());

        event(new CreatedContentEvent(HUB_WAREHOUSE_MODULE_SCREEN_NAME, $request, $hubWarehouse));

        return $response
            ->setPreviousUrl(route('hub-warehouse.index'))
            ->setNextUrl(route('hub-warehouse.edit', $hubWarehouse->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(HubWarehouse $hubWarehouse, FormBuilder $formBuilder)
    {
        if (!request()->user()->hasPermission('hub-warehouse.all-permissions')) {
            abort_if(!in_array($hubWarehouse->id, request()->user()->userHub()->pluck('hub_id')->toArray()), 403, 'Không có quyền chỉnh sửa Hub này');
        }
        $this->pageTitle(trans('Chỉnh sửa :name', ['name' => $hubWarehouse->name]));

        return $formBuilder->create(HubWarehouseForm::class, ['model' => $hubWarehouse])->renderForm();
    }

    public function update(HubWarehouse $hubWarehouse, HubWarehouseRequest $request, BaseHttpResponse $response)
    {
        $hubWarehouse->fill($request->input());

        $hubWarehouse->save();

        event(new UpdatedContentEvent(HUB_WAREHOUSE_MODULE_SCREEN_NAME, $request, $hubWarehouse));

        return $response
            ->setPreviousUrl(route('hub-warehouse.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(HubWarehouse $hubWarehouse, Request $request, BaseHttpResponse $response)
    {
        try {
            $hubWarehouse->delete();

            event(new DeletedContentEvent(HUB_WAREHOUSE_MODULE_SCREEN_NAME, $request, $hubWarehouse));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function getAllHub()
    {
        $hub = HubWarehouse::where('status', HubStatusEnum::ACTIVE)->get();
        return response()->json($hub, 200);
    }
    public function getProductByWarehouse(int|string $id, Request $request)
    {
        $model = $request->input('model');
        $keySearch = $request->input('keySearch');
        if ($model != SaleProduct::class) {
            $query = ['stock_id' => $id];
        }
        else{
            $query = ['warehouse_id'=> $id];
        }
        if ($keySearch != '') {
            $product = $model::where($query)->with([
                'product.variationInfo',
                'product.parentProduct.categories',
                'product.productAttribute',
                'product',
            ])->whereHas(
                'product', function ($query) use ($keySearch) {
                    $query->where('status', 'published')
                        ->where(function ($q) use ($keySearch) {
                        return $q->where('name', 'LIKE', "%" . $keySearch . "%")->orWhere('sku', 'LIKE', "%" . $keySearch . "%");
                    });
                },
            )->where('quantity', '>', 0)->limit(50)->get();
        } else {
            $product = $model::where($query)->with(
                [
                    'product' => function ($query) {
                        $query->where('status', 'published');
                    },
                    'product.variationInfo',
                    'product.parentProduct.categories',
                    'product.productAttribute',
                ]
            )->where('quantity', '>', 0)->limit(50)->get();
        }

        return response()->json(['dataDetail' => $product], 200, );
        // $products = ProductBatch::with([
        //     'product' => function ($query) {
        //         $query->where('status', 'published');
        //     },
        //     'product.productAttribute',
        //     'listProduct'
        // ])
        //     ->where([
        //         'warehouse_type' => $model,
        //         'warehouse_id' => $id,
        //         'status' => ProductBatchStatusEnum::INSTOCK,

        //     ])->where('quantity', '>', 0)
        //     ->get();

        // $productDetail = ProductBatchDetail::with([
        //     'product' => function ($query) {
        //         $query->where('status', 'published');
        //     },
        //     'product.variationInfo',
        //     'product.parentProduct',
        //     'product.productAttribute',
        // ])
        //     ->whereHas('productBatch', function ($query) use ($model, $id) {
        //         $query->where('warehouse_type', $model)
        //             ->where('warehouse_id', $id)
        //             ->where('status', ProductBatchStatusEnum::INSTOCK);
        //     })
        //     ->select('product_id')
        //     ->selectRaw('COUNT(*) as batch_count')
        //     ->groupBy('product_id')
        //     ->get();
        // return response()->json(['data' => $products, 'dataDetail' => $productDetail], 200);
    }

    public function detail($id)
    {

    }
}
