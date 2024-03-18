<?php

namespace Botble\WarehouseFinishedProducts\Http\Controllers;

use Botble\WarehouseFinishedProducts\Http\Requests\HubRequest;
use Botble\WarehouseFinishedProducts\Models\Hub;
use Botble\Base\Facades\PageTitle;
use Illuminate\Http\Request;
use Exception;
use Botble\WarehouseFinishedProducts\Tables\HubTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\WarehouseFinishedProducts\Forms\HubForm;
use Botble\Base\Forms\FormBuilder;

class HubController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('HUB'), route('hub.index'));
    }
    public function index(HubTable $table)
    {

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Thêm HUB mới'));

        return $formBuilder->create(HubForm::class)->renderForm();
    }

    public function store(HubRequest $request, BaseHttpResponse $response)
    {
        $hub = Hub::query()->create($request->input());

        event(new CreatedContentEvent(HUB_MODULE_SCREEN_NAME, $request, $hub));

        return $response
            ->setPreviousUrl(route('hub.index'))
            ->setNextUrl(route('hub.edit', $hub->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Hub $hub, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('Cập nhật HUB :name', ['name' => $hub->name]));

        return $formBuilder->create(HubForm::class, ['model' => $hub])->renderForm();
    }

    public function update(Hub $hub, HubRequest $request, BaseHttpResponse $response)
    {
        $hub->fill($request->input());

        $hub->save();

        event(new UpdatedContentEvent(HUB_MODULE_SCREEN_NAME, $request, $hub));

        return $response
            ->setPreviousUrl(route('hub.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Hub $hub, Request $request, BaseHttpResponse $response)
    {
        try {
            $hub->delete();

            event(new DeletedContentEvent(HUB_MODULE_SCREEN_NAME, $request, $hub));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
    public function getAllHubs()
    {
        $hubs = Hub::where('status', 'active')->get();
        return response()->json(['data' => $hubs]);
    }
}
