<?php

namespace Botble\Showroom\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Showroom\Enums\ShowroomStatusEnum;
use Botble\Showroom\Forms\ShowroomWarehouseForm;
use Botble\Showroom\Http\Requests\CreateShowroomProductRequest;
use Botble\Showroom\Http\Requests\ShowroomWarehouseRequest;
use Botble\Showroom\Models\Showroom;
use Botble\Showroom\Models\ShowroomProduct;
use Botble\Showroom\Models\ShowroomUser;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Tables\ShowroomProductBatchTable;
use Botble\Showroom\Tables\ShowroomProductOddTable;
use Botble\Showroom\Tables\ShowroomProductTable;
use Botble\Showroom\Tables\ShowroomWarehouseTable;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// use Botble\Showroom\Models\ShowroomWarehouse;
// use Botble\Showroom\Tables\ShowroomWarehouseTable;

// use Botble\Showroom\Models\ShowroomWarehouse;
// use Botble\Showroom\Tables\ShowroomWarehouseTable;

class ShowroomWarehouseController extends BaseController
{
    private $titleName;
    private $messageNoti;

    public function __construct()
    {
        $this->titleName = trans('plugins/showroom::showroom.page_title');
        $this->messageNoti = trans('plugins/showroom::showroom.message');
    }

    public function index(ShowroomWarehouseTable $table)
    {
        PageTitle::setTitle($this->titleName['warehouse_showroom']);

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle($this->titleName['create_warehouse_showrooms']);

        return $formBuilder->create(ShowroomWarehouseForm::class)->renderForm();
    }

    public function store(ShowroomWarehouseRequest $request, BaseHttpResponse $response)
    {
        $showroom = ShowroomWarehouse::query()->create($request->input());

        event(new CreatedContentEvent(SHOWROOM_MODULE_SCREEN_NAME, $request, $showroom));

        return $response
            ->setPreviousUrl(route('showroom-warehouse.index'))
            ->setNextUrl(route('showroom-warehouse.edit', $showroom->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ShowroomWarehouse $showroomWarehouse, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans($this->titleName['edit_warehouse_showrooms'], ['name' => $showroomWarehouse->name]));
        $showroomWarehouse = $this->fillterWarehouseByShowroom($showroomWarehouse);
        return $formBuilder->create(ShowroomWarehouseForm::class, ['model' => $showroomWarehouse])->renderForm();
    }

    public function update(ShowroomWarehouse $showroomWarehouse, ShowroomWarehouseRequest $request, BaseHttpResponse $response)
    {
        $showroomWarehouse->fill($request->input());

        $showroomWarehouse->save();

        event(new UpdatedContentEvent(SHOWROOM_MODULE_SCREEN_NAME, $request, $showroomWarehouse));

        return $response
            ->setPreviousUrl(route('showroom-warehouse.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ShowroomWarehouse $showroomWarehouse, Request $request, BaseHttpResponse $response)
    {
        try {
            $showroomWarehouse->delete();

            event(new DeletedContentEvent(SHOWROOM_MODULE_SCREEN_NAME, $request, $showroomWarehouse));

            return $response->setMessage($this->messageNoti['delete_success']);
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($this->messageNoti['delete_error']);
        }
    }

    private function fillterWarehouseByShowroom($query)
    {
        if (!request()->user()->hasPermission('warehouse-finished-products.warehouse-all')) {
            $showrooomUsers = ShowroomUser::where('user_id', Auth::id())->get();
            $showroomIds = $showrooomUsers->pluck('showroom_id')->toArray();
            if (!in_array($query->showroom_id, $showroomIds)) {
                abort(403, 'Không có quyền truy cập kho này');
            }
        }
        return $query;
    }

    public function createManual(ShowroomWarehouse $showroom, Request $request, $id, BaseHttpResponse $response)
    {
        PageTitle::setTitle(trans('Thêm sản phẩm thủ công'));

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
        // $listShowroom = request()->user()->agent->pluck('id');

        $agencyProduct = ShowroomProduct::where('warehouse_id', $id)->get();
        $showroomWarehouse = ShowroomWarehouse::where('id', $id)->first();

        return view('plugins/showroom::products.create', compact('agencyProduct', 'showroomWarehouse'));
    }

    public function storeManual(CreateShowroomProductRequest $request, BaseHttpResponse $response)
    {
        $dataReq = $request->input();
        $dataProduct = $dataReq['products'];
        $today = Carbon::today();
        DB::beginTransaction();
        try {

            if (is_array($dataProduct) && count($dataProduct) > 0) {
                foreach ($dataProduct as $product) {
                    if ($product['showroom_product_id'] != null) {
                        ShowroomProduct::where('id', $product['showroom_product_id'])
                            ->increment('quantity_not_qrcode', $product['select_qty']);
                        // showroomProduct::where('id', $product['showroom_product_id'])->update([
                        //     'warehouse_id' => $product['warehouse_id'],
                        // ]);
                    } else {
                        ShowroomProduct::insert([
                            'warehouse_id' => $product['warehouse_id'],
                            'product_id' => $product['product_id'],
                            'quantity_qrcode' => 0,
                            'where_type' => Showroom::class,
                            'where_id' => $product['showroom_id'],
                            'quantity_not_qrcode' => $product['select_qty'],
                            'quantity_sold_not_qrcode' => 0,
                            'created_at' => $today,
                        ]);
                    }
                }
            }
            DB::commit();
            return $response
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME:' + $e);
        }
    }

    public function detailBatchInStock(ShowroomWarehouse $stock, Request $request, ShowroomProductBatchTable $table)
    {
        $this->pageTitle('Chi tiết lô hàng có trong kho - ' . $stock->name);
        $request->merge(['id' => $stock->id]);

        return $table->renderTable();
    }
    public function detailProductInStock(ShowroomWarehouse $stock, Request $request, ShowroomProductTable $table)
    {
        $this->pageTitle('Chi tiết sản phẩm trong kho - ' . $stock->name);
        $request->merge(['id' => $stock->id]);

        return $table->renderTable();
    }

    public function detailOddInStock(ShowroomWarehouse $stock, Request $request, ShowroomProductOddTable $table)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/showroom/js/showroom-warehouse.js',
        ]);
        $this->pageTitle(trans('Chi tiết sản phẩm lẻ có trong kho / ' . $stock->name));
        $request->merge(['id' => $stock->id]);

        return $table->renderTable();
    }

    public function reduceQuantity(Request $request, BaseHttpResponse $response)
    {
        $quantity = $request->input()['quantity'];
        $id = $request->input()['id'];

        $productInStock = ShowroomProduct::where('id', $id)->first();

        if ($productInStock) {
            $productInStock->decrement('quantity_not_qrcode', $quantity);
            $productInStock->increment('quantity_sold_not_qrcode', $quantity);
        }

        // ShowroomProduct::where('id', $request->input()['id'])
        //     ->decrement('quantity_not_qrcode', $request->input()['quantity']);

        return $response
            ->setPreviousUrl(route('showroom-warehouse.index'))
            ->setMessage(trans('Chỉnh sửa số lượng thành công'));
        // $request->merge(['id' => $stock->id]);

        // return $table->renderTable();
    }

    public function getWarehouseByShowroom($id)
    {
        $warehouses = ShowroomWarehouse::where('showroom_id', $id)->where('status', ShowroomStatusEnum::ACTIVE)->get();
        return response()->json($warehouses, 200);
    }
    public function getAllWarehouseShowroom()
    {
        $warehouses = ShowroomWarehouse::where('status', ShowroomStatusEnum::ACTIVE)->get();
        return response()->json(['data' => $warehouses, 'success' => 1], 200);

    }
}
