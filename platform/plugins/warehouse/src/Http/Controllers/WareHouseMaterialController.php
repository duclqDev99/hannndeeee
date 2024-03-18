<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Warehouse\Http\Requests\WarehouseMaterialRequest;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Base\Facades\PageTitle;
use Botble\Warehouse\Tables\MaterialInWarehouseTable;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\WarehouseMaterialTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\WarehouseMaterialForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Warehouse\Models\Material;

class WareHouseMaterialController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Kho hàng'), route('warehouse-material.index'));
    }
    public function index(WarehouseMaterialTable $table)
    {

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::inventory_material.create'));

        return $formBuilder->create(WarehouseMaterialForm::class)->renderForm();
    }

    public function store(WarehouseMaterialRequest $request, BaseHttpResponse $response)
    {
        $materialWarehouse = MaterialWarehouse::query()->create($request->input());

        event(new CreatedContentEvent(INVENTORY_MATERIAL_MODULE_SCREEN_NAME, $request, $materialWarehouse));

        return $response
            ->setPreviousUrl(route('warehouse-material.index'))
            ->setNextUrl(route('warehouse-material.edit', $materialWarehouse->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(MaterialWarehouse $warehouseMaterial, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $warehouseMaterial->name]));

        return $formBuilder->create(WarehouseMaterialForm::class, ['model' => $warehouseMaterial])->renderForm();
    }

    public function update(MaterialWarehouse $warehouseMaterial, WarehouseMaterialRequest $request, BaseHttpResponse $response)
    {
        $warehouseMaterial->fill($request->input());

        $warehouseMaterial->save();

        event(new UpdatedContentEvent(INVENTORY_MATERIAL_MODULE_SCREEN_NAME, $request, $warehouseMaterial));

        return $response
            ->setPreviousUrl(route('warehouse-material.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(MaterialWarehouse $materialWarehouse, Request $request, BaseHttpResponse $response)
    {

        try {
            $materialWarehouse->delete();
            dd($materialWarehouse->delete());
            event(new DeletedContentEvent(INVENTORY_MATERIAL_MODULE_SCREEN_NAME, $request, $materialWarehouse));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function detail(int|string $id, Request $request, MaterialInWarehouseTable $table)
    {
        // $materialWarehouses = MaterialWarehouse::find($id)->materials;
        $materialWarehouses = MaterialWarehouse::find($id);
        $this->pageTitle('Chi tiết kho hàng ' . $materialWarehouses->name);
        $request->merge(['id' => $id]);
        return $table->renderTable();
    }
    public function getAllMaterialFinished(BaseHttpResponse $response)
    {
        $materialFinished = MaterialWarehouse::get();
        return $response
            ->setData($materialFinished);
    }
}
