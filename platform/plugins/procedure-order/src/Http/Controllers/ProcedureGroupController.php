<?php

namespace Botble\ProcedureOrder\Http\Controllers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\ProcedureOrder\Http\Requests\ProcedureGroupRequest;
use Botble\ProcedureOrder\Models\ProcedureGroup;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\ProcedureOrder\Tables\ProcedureGroupTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\ProcedureOrder\Forms\ProcedureGroupForm;
use Botble\Base\Forms\FormBuilder;
use Botble\Department\Models\Department;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\ProcedureOrder\Tables\ProcedureOrderTable;
use Illuminate\Support\Facades\DB;
use Botble\ProcedureOrder\Forms\ProcedureOrderForm;
use Botble\ProcedureOrder\Http\Requests\ProcedureOrderRequest;

use function Laravel\Prompts\error;

class ProcedureGroupController extends BaseController
{
    public function index(ProcedureGroupTable $table)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/procedure-order/js/script.js',
            'vendor/core/plugins/procedure-order/js/procedure-order.js',
        ]);
        Assets::usingVueJS();
        PageTitle::setTitle(trans('plugins/procedure-order::procedure-group.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {

        PageTitle::setTitle(trans('plugins/procedure-order::procedure-group.create'));

        return $formBuilder->create(ProcedureGroupForm::class)->renderForm();
    }

    public function store(ProcedureGroupRequest $request, BaseHttpResponse $response)
    {
        $procedureGroup = ProcedureGroup::query()->create($request->input());

        event(new CreatedContentEvent(PROCEDURE_GROUP_MODULE_SCREEN_NAME, $request, $procedureGroup));

        return $response
            ->setPreviousUrl(route('procedure-groups.index'))
            ->setNextUrl(route('procedure-groups.edit', $procedureGroup->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ProcedureGroup $procedureGroup, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $procedureGroup->name]));

        return $formBuilder->create(ProcedureGroupForm::class, ['model' => $procedureGroup])->renderForm();
    }

    public function update(ProcedureGroup $procedureGroup, ProcedureGroupRequest $request, BaseHttpResponse $response)
    {
        $procedureGroup->fill($request->input());

        $procedureGroup->save();

        event(new UpdatedContentEvent(PROCEDURE_GROUP_MODULE_SCREEN_NAME, $request, $procedureGroup));

        return $response
            ->setPreviousUrl(route('procedure-groups.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ProcedureGroup $procedureGroup, Request $request, BaseHttpResponse $response)
    {
        try {
            $procedureGroup->delete();

            event(new DeletedContentEvent(PROCEDURE_GROUP_MODULE_SCREEN_NAME, $request, $procedureGroup));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function updateStatus($id, ProcedureGroup $procedureGroup, Request $request, BaseHttpResponse $response)
    {
        try {
            DB::beginTransaction();

            $procedureGroup = ProcedureGroup::findOrFail($id);

            $newStatus = ($procedureGroup->status == BaseStatusEnum::PUBLISHED) ? BaseStatusEnum::PENDING : BaseStatusEnum::PUBLISHED;

            $procedureGroup->update(['status' => $newStatus]);

            DB::commit();

            event(new UpdatedContentEvent(PROCEDURE_GROUP_MODULE_SCREEN_NAME, $request, $procedureGroup));

            return $response
                ->setPreviousUrl(route('procedure-groups.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('PROCEDURE_GROUP_MODULE_SCREEN_NAME: ' . $e);
        }
    }


    public function getProcedureById($id)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/procedure-order/js/order-script.js',
        ]);
        $procedureOrder = ProcedureOrder::where('group_id', $id)->paginate(10);

        PageTitle::setTitle(trans('plugins/procedure-order::procedure-order.name'));

        return view('plugins/procedure-order::table-procedure-order', compact('procedureOrder', 'id'))->render();
    }

    public function getFlowchartById($id, Request $request, BaseHttpResponse $response)
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/procedure-order/js/procedure-order.js',
        ]);
        Assets::usingVueJS();

        PageTitle::setTitle(trans('plugins/procedure-order::procedure-order.name'));

        $procedureOrder = ProcedureOrder::where('group_id', $id)->get();
        $departmanets = Department::where('status', 'published')->get();

        return view('plugins/procedure-order::procedure-order', compact('procedureOrder', 'departmanets'))->render();
    }

    public function orderCreate($id, FormBuilder $formBuilder, Request $request)
    {
        $request->merge(['id' => $id]);
        PageTitle::setTitle(trans('plugins/procedure-order::procedure-order.create'));

        return $formBuilder->create(ProcedureOrderForm::class)->renderForm();
    }

    public function orderStore(ProcedureOrderRequest $request, BaseHttpResponse $response)
    {
        $procedureGroup = ProcedureOrder::query()->create($request->input());

        event(new CreatedContentEvent(PROCEDURE_GROUP_MODULE_SCREEN_NAME, $request, $procedureGroup));

        return redirect()->route('procedure-groups.get-procedure-by-id', $request->input()['group_id']);
    }

    public function orderEdit(FormBuilder $formBuilder, Request $request, $id)
    {
        $procedureOrder = ProcedureOrder::find($id);
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $procedureOrder->name]));

        return $formBuilder->create(ProcedureOrderForm::class, ['model' => $procedureOrder])->renderForm();
    }

    public function orderUpdate($id, ProcedureOrderRequest $request, BaseHttpResponse $response)
    {
        $procedureOrder = ProcedureOrder::find($id);
        $procedureOrder->fill($request->input());
        $procedureOrder->save();

        event(new UpdatedContentEvent(PROCEDURE_ORDER_MODULE_SCREEN_NAME, $request, $procedureOrder));

        return redirect()->route('procedure-groups.get-procedure-by-id', $request->input()['group_id']);
    }

    public function orderDelete($id, Request $request, BaseHttpResponse $response)
    {
        $procedureOrder = ProcedureOrder::find($id);
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
