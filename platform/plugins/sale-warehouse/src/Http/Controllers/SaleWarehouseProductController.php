<?php

namespace Botble\SaleWarehouse\Http\Controllers;

use Botble\SaleWarehouse\Http\Requests\SaleWarehouseRequest;
use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\SaleWarehouse\Tables\SaleWarehouseProductTable;
use Illuminate\Http\Request;
use Exception;
use Botble\SaleWarehouse\Tables\SaleWarehouseTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\SaleWarehouse\Forms\SaleWarehouseForm;
use Botble\Base\Forms\FormBuilder;

class SaleWarehouseProductController extends BaseController
{
    public function index(SaleWarehouseProductTable $table)
    {
        PageTitle::setTitle('Danh sách sản phẩm kho sale');

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sale-warehouse::sale-warehouse.create'));

        return $formBuilder->create(SaleWarehouseForm::class)->renderForm();
    }

    public function store(SaleWarehouseRequest $request, BaseHttpResponse $response)
    {
        $saleWarehouse = SaleWarehouse::query()->create($request->input());

        event(new CreatedContentEvent(SALE_WAREHOUSE_MODULE_SCREEN_NAME, $request, $saleWarehouse));

        return $response
            ->setPreviousUrl(route('sale-warehouse.index'))
            ->setNextUrl(route('sale-warehouse.edit', $saleWarehouse->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(SaleWarehouse $saleWarehouse, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $saleWarehouse->name]));

        return $formBuilder->create(SaleWarehouseForm::class, ['model' => $saleWarehouse])->renderForm();
    }

    public function update(SaleWarehouse $saleWarehouse, SaleWarehouseRequest $request, BaseHttpResponse $response)
    {
        $saleWarehouse->fill($request->input());

        $saleWarehouse->save();

        event(new UpdatedContentEvent(SALE_WAREHOUSE_MODULE_SCREEN_NAME, $request, $saleWarehouse));

        return $response
            ->setPreviousUrl(route('sale-warehouse.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(SaleWarehouse $saleWarehouse, Request $request, BaseHttpResponse $response)
    {
        try {
            $saleWarehouse->delete();

            event(new DeletedContentEvent(SALE_WAREHOUSE_MODULE_SCREEN_NAME, $request, $saleWarehouse));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
