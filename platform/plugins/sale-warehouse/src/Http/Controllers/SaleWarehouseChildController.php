<?php

namespace Botble\SaleWarehouse\Http\Controllers;

use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Http\Requests\SaleWarehouseChildRequest;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\SaleWarehouse\Tables\SaleWarehouseChildTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\SaleWarehouse\Forms\SaleWarehouseChildForm;
use Botble\Base\Forms\FormBuilder;

class SaleWarehouseChildController extends BaseController
{
    public function index(SaleWarehouseChildTable $table)
    {
        PageTitle::setTitle(trans('plugins/sale-warehouse::sale-warehouse-child.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sale-warehouse::sale-warehouse-child.create'));

        return $formBuilder->create(SaleWarehouseChildForm::class)->renderForm();
    }

    public function store(SaleWarehouseChildRequest $request, BaseHttpResponse $response)
    {
        $saleWarehouseChild = SaleWarehouseChild::query()->create($request->input());

        event(new CreatedContentEvent(SALE_WAREHOUSE_CHILD_MODULE_SCREEN_NAME, $request, $saleWarehouseChild));

        return $response
            ->setPreviousUrl(route('sale-warehouse-child.index'))
            ->setNextUrl(route('sale-warehouse-child.edit', $saleWarehouseChild->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(SaleWarehouseChild $saleWarehouseChild, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $saleWarehouseChild->name]));

        return $formBuilder->create(SaleWarehouseChildForm::class, ['model' => $saleWarehouseChild])->renderForm();
    }

    public function update(SaleWarehouseChild $saleWarehouseChild, SaleWarehouseChildRequest $request, BaseHttpResponse $response)
    {
        $saleWarehouseChild->fill($request->input());

        $saleWarehouseChild->save();

        event(new UpdatedContentEvent(SALE_WAREHOUSE_CHILD_MODULE_SCREEN_NAME, $request, $saleWarehouseChild));

        return $response
            ->setPreviousUrl(route('sale-warehouse-child.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(SaleWarehouseChild $saleWarehouseChild, Request $request, BaseHttpResponse $response)
    {
        try {
            $saleWarehouseChild->delete();

            event(new DeletedContentEvent(SALE_WAREHOUSE_CHILD_MODULE_SCREEN_NAME, $request, $saleWarehouseChild));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function getAllWarehouse()
    {
        $warehouse = SaleWarehouseChild::where(['status' => SaleWarehouseStatusEnum::ACTIVE()])->get();
        return response()->json(['data' => $warehouse, 'err' => 0], 200);
    }
}
