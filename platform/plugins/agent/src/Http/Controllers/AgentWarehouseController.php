<?php

namespace Botble\Agent\Http\Controllers;

use Botble\Agent\Enums\AgentStatusEnum;
use Botble\Agent\Http\Requests\AgentRequest;
use Botble\Agent\Models\Agent;
use Botble\Agent\Models\AgentUser;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Agent\Forms\AgentForm;
use Botble\Agent\Forms\AgentWarehouseForm;
use Botble\Agent\Http\Requests\AgentWarehouseRequest;
use Botble\Agent\Http\Requests\CreateAgentProductRequest;
use Botble\Agent\Models\AgentProduct;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Agent\Tables\AgentWarehouseTable;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FormBuilder;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Agent\Tables\AgentProductBatchTable;
use Botble\Agent\Tables\AgentProductOddTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AgentWarehouseController extends BaseController
{
    public function index(AgentWarehouseTable $table)
    {

        PageTitle::setTitle(trans('plugins/agent::agent.name'));

        return $table->renderTable();
    }

    public function getAgentWarehouse(Request $request)
    {
        $agent_id = $request->agent_id ?? null;

        $agentWarehouses = AgentWarehouse::select('id')
            ->when($agent_id, fn ($q) => $q->where('agent_id', $agent_id))
            ->get();

        return response()->json(['data' => $agentWarehouses->map(fn ($item) => $item->id)], 200);
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle('Tạo kho mới');

        $selectId = request()->input('select_id', null);
        $agentUser = getListAgentIdByUser();

        $listAgent = Agent::query()
            ->wherePublished()
            ->when(!request()->user()->isSuperUser(), function($query) use ($agentUser) {
                $query->whereIn('id', $agentUser);
            })
            ->pluck('name', 'id');

        if ($selectId !== null && $listAgent->has($selectId)) {
            return $formBuilder->create(AgentWarehouseForm::class)->renderForm();
        } else {
            return Redirect::to('/admin');
        }
    }

    public function store(AgentWarehouseRequest $request, BaseHttpResponse $response)
    {
        $agent = AgentWarehouse::query()->create($request->input());

        event(new CreatedContentEvent(AGENT_WAREHOUSE_MODULE_SCREEN_NAME, $request, $agent));

        return $response
            ->setPreviousUrl(route('agent-warehouse.index'))
            ->setNextUrl(route('agent-warehouse.edit', $agent->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(AgentWarehouse $agentWarehouse, FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        $selectId = $agentWarehouse->agent_id;
        $agentUser = getListAgentIdByUser();
        $listAgent = Agent::query()
            ->wherePublished()
            ->when(!request()->user()->isSuperUser(), function($query) use ($agentUser) {
                $query->whereIn('id', $agentUser);
            })
            ->pluck('name', 'id');

        if ($selectId !== null && $listAgent->has($selectId)) {
            PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $agentWarehouse->name]));
            $agentWarehouse = $this->fillterWarehouseByAgent($agentWarehouse, $response);
            return $formBuilder->create(AgentWarehouseForm::class, ['model' => $agentWarehouse])->renderForm();
        } else {
            return Redirect::to('/admin');
        }
    }

    public function update(AgentWarehouse $agentWarehouse, AgentWarehouseRequest $request, BaseHttpResponse $response)
    {
        $agentWarehouse->fill($request->input());

        $agentWarehouse->save();

        event(new UpdatedContentEvent(AGENT_WAREHOUSE_MODULE_SCREEN_NAME, $request, $agentWarehouse));

        return $response
            ->setPreviousUrl(route('agent-warehouse.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(AgentWarehouse $agent, Request $request, BaseHttpResponse $response)
    {
        try {
            $agent->delete();

            event(new DeletedContentEvent(AGENT_WAREHOUSE_MODULE_SCREEN_NAME, $request, $agent));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    private function fillterWarehouseByAgent($query)
    {
        $response = new BaseHttpResponse;
        if (!request()->user()->hasPermission('agent.all')) {
            $agnetUsers = AgentUser::where('user_id', \Auth::id())->get();
            $agentIds = $agnetUsers->pluck('agent_id')->toArray();
            if (!in_array($query->agent_id, $agentIds)) {
                return $response
                    ->setError()
                    ->setMessage(__('Không có quyền truy cập kho trong đại lý này'));
            }
        }
        return $query;
    }

    public function createManual(AgentWarehouse $agent, Request $request, $id, BaseHttpResponse $response)
    {
        PageTitle::setTitle(trans('Thêm sản phẩm thủ công'));

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

        $agencyProduct = AgentProduct::where('warehouse_id', $id)->get();

        $agentWarehouse = AgentWarehouse::where('id', $id)->first();

        return view('plugins/agent::products.create', compact('agencyProduct', 'agentWarehouse'));
    }

    public function storeManual(CreateAgentProductRequest $request, BaseHttpResponse $response)
    {
        $dataReq = $request->input();
        $dataProduct = $dataReq['products'];
        $today = Carbon::today();
        DB::beginTransaction();
        try {
            $agent = AgentWarehouse::query()->find($dataReq['agent_warehouse']);
            if (is_array($dataProduct) && count($dataProduct) > 0) {
                foreach ($dataProduct as $product) {
                    if ($product['agent_product_id'] != null) {
                        AgentProduct::where('id', $product['agent_product_id'])
                            ->increment('quantity_not_qrcode', $product['select_qty']);
                        // AgentProduct::where('id', $product['agent_product_id'])->update([
                        //     'warehouse_id' => $product['warehouse_id'],
                        // ]);
                    } else {
                        AgentProduct::insert([
                            'warehouse_id' => $product['warehouse_id'],
                            'product_id' => $product['product_id'],
                            'quantity_qrcode' => 0,
                            'quantity_not_qrcode' => $product['select_qty'],
                            'created_at' => $today,
                            'where_type' => Agent::class,
                            'where_id' => $agent->id,
                        ]);
                    }
                }
            }
            DB::commit();
            return $response
                ->setPreviousUrl(route('agent-product.index'))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (Exception  $e) {
            DB::rollBack();
            throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME:' + $e);
        }
    }

    public function detailBatchInStock(AgentWarehouse $stock, Request $request, AgentProductBatchTable $table)
    {
        $this->pageTitle('Chi tiết lô hàng có trong kho / ' . $stock->name);
        $request->merge(['id' => $stock->id]);

        return $table->renderTable();
    }

    public function detailOddInStock(AgentWarehouse $stock, Request $request, AgentProductOddTable $table)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/agent/js/agent-warehouse.js',
        ]);
        $this->pageTitle(trans('Chi tiết sản phẩm lẻ có trong kho - ' . $stock->name));
        $request->merge(['id' => $stock->id]);

        return $table->renderTable();
    }

    public function reduceQuantity(Request $request, BaseHttpResponse $response)
    {
        $quantity = $request->input()['quantity'];
        $id = $request->input()['id'];

        $productInStock = AgentProduct::where('id', $id)->first();

        if ($productInStock) {
            $productInStock->decrement('quantity_not_qrcode', $quantity);
            $productInStock->increment('quantity_sold_not_qrcode', $quantity);
        }

        // AgentProduct::where('id', $request->input()['id'])
        //     ->decrement('quantity_not_qrcode', $request->input()['quantity']);

        return $response
            ->setPreviousUrl(route('agent-warehouse.index'))
            ->setMessage(trans('Chỉnh sửa số lượng thành công'));
        // $request->merge(['id' => $stock->id]);

        // return $table->renderTable();
    }
    public function getWarehouseByAgent($id)
    {
        $warehouses = AgentWarehouse::where('agent_id', $id)->get();
        return response()->json($warehouses, 200);
    }
    public function getAllAgentWarehouse()
    {
        $warehouse = AgentWarehouse::where('status', AgentStatusEnum::ACTIVE)->get();
        return response()->json(['data' => $warehouse, 'success' => 1], 200);
    }
}
