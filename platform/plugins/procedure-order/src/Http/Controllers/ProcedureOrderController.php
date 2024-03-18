<?php

namespace Botble\ProcedureOrder\Http\Controllers;

use Botble\ProcedureOrder\Http\Requests\ProcedureOrderRequest;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\ProcedureOrder\Tables\ProcedureOrderTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\ProcedureOrder\Forms\ProcedureOrderForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Theme\Asset;

class ProcedureOrderController extends BaseController
{
    public function index(ProcedureOrderTable $table)
    {
        Assets::addStylesDirectly([
            'vendor/core/plugins/procedure-order/css/orgchart.css',
            'vendor/core/plugins/procedure-order/css/procedure-order.css',
            ])
        ->addScriptsDirectly([
            'vendor/core/plugins/procedure-order/js/jquery.orgchart.js',
            'vendor/core/plugins/procedure-order/js/jquery-1.11.1.min.js',
            'vendor/core/plugins/procedure-order/js/procedure-order.js',

        ]);
        Assets::usingVueJS();
        $procedureOrder = ProcedureOrder::all();
        PageTitle::setTitle(trans('plugins/procedure-order::procedure-order.name'));


        // return view(,compact(procedureOrder))
        // return view('plugins/procedure-order::procedure-order', compact('procedureOrder'))->render();
        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/procedure-order::procedure-order.create'));

        return $formBuilder->create(ProcedureOrderForm::class)->renderForm();
    }

    public function store(ProcedureOrderRequest $request, BaseHttpResponse $response)
    {
        $procedureOrder = ProcedureOrder::query()->create($request->input());

        event(new CreatedContentEvent(PROCEDURE_ORDER_MODULE_SCREEN_NAME, $request, $procedureOrder));

        return $response
            ->setPreviousUrl(route('procedure-order.index'))
            ->setNextUrl(route('procedure-order.edit', $procedureOrder->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ProcedureOrder $procedureOrder, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $procedureOrder->name]));

        return $formBuilder->create(ProcedureOrderForm::class, ['model' => $procedureOrder])->renderForm();
    }

    public function update(ProcedureOrder $procedureOrder, ProcedureOrderRequest $request, BaseHttpResponse $response)
    {
        $procedureOrder->fill($request->input());

        $procedureOrder->save();

        event(new UpdatedContentEvent(PROCEDURE_ORDER_MODULE_SCREEN_NAME, $request, $procedureOrder));

        return $response
            ->setPreviousUrl(route('procedure-order.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ProcedureOrder $procedureOrder, Request $request, BaseHttpResponse $response)
    {
        try {
            $procedureOrder->delete();

            event(new DeletedContentEvent(PROCEDURE_ORDER_MODULE_SCREEN_NAME, $request, $procedureOrder));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
