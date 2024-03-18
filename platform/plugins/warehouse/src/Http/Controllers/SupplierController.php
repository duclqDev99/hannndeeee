<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Warehouse\Http\Requests\SupplierRequest;
use Botble\Warehouse\Models\Supplier;
use Botble\Base\Facades\PageTitle;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\SupplierTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\SupplierForm;
use Botble\Base\Forms\FormBuilder;

class SupplierController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Nhà cung cấp'), route('supplier.index'));
    }
    public function index(SupplierTable $table)
    {

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::supplier.create'));

        return $formBuilder->create(SupplierForm::class)->renderForm();
    }

    public function store(SupplierRequest $request, BaseHttpResponse $response)
    {
        $supplier = Supplier::query()->create($request->input());

        event(new CreatedContentEvent(SUPPLIER_MODULE_SCREEN_NAME, $request, $supplier));

        return $response
            ->setPreviousUrl(route('supplier.index'))
            ->setNextUrl(route('supplier.edit', $supplier->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Supplier $supplier, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $supplier->name]));

        return $formBuilder->create(SupplierForm::class, ['model' => $supplier])->renderForm();
    }

    public function update(Supplier $supplier, SupplierRequest $request, BaseHttpResponse $response)
    {
        $supplier->fill($request->input());

        $supplier->save();

        event(new UpdatedContentEvent(SUPPLIER_MODULE_SCREEN_NAME, $request, $supplier));

        return $response
            ->setPreviousUrl(route('supplier.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Supplier $supplier, Request $request, BaseHttpResponse $response)
    {
        try {
            $supplier->delete();

            event(new DeletedContentEvent(SUPPLIER_MODULE_SCREEN_NAME, $request, $supplier));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
