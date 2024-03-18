<?php

namespace Botble\Sales\Http\Controllers;

use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Sales\Tables\SalesTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Sales\Forms\SalesForm;
use Botble\Base\Forms\FormBuilder;

class SalesController extends BaseController
{
    public function index(SalesTable $table)
    {
        PageTitle::setTitle(trans('plugins/sales::sales.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/sales::sales.create'));

        return $formBuilder->create(SalesForm::class)->renderForm();
    }

    public function store(SalesRequest $request, BaseHttpResponse $response)
    {
        $sales = Sales::query()->create($request->input());

        event(new CreatedContentEvent(SALES_MODULE_SCREEN_NAME, $request, $sales));

        return $response
            ->setPreviousUrl(route('sales.index'))
            ->setNextUrl(route('sales.edit', $sales->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Sales $sales, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $sales->name]));

        return $formBuilder->create(SalesForm::class, ['model' => $sales])->renderForm();
    }

    public function update(Sales $sales, SalesRequest $request, BaseHttpResponse $response)
    {
        $sales->fill($request->input());

        $sales->save();

        event(new UpdatedContentEvent(SALES_MODULE_SCREEN_NAME, $request, $sales));

        return $response
            ->setPreviousUrl(route('sales.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Sales $sales, Request $request, BaseHttpResponse $response)
    {
        try {
            $sales->delete();

            event(new DeletedContentEvent(SALES_MODULE_SCREEN_NAME, $request, $sales));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
