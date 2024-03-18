<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Warehouse\Http\Requests\ProcessingHouseRequest;
use Botble\Warehouse\Models\ProcessingHouse;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\ProcessingHouseTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\ProcessingHouseForm;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\DB;

class ProcessingHouseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->breadcrumb()
            ->add(trans('Nhà gia công'), route('processing_house.index'));
    }
    public function index(ProcessingHouseTable $table)
    {
        $this->pageTitle(trans('plugins/warehouse::material.processing_house.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::material.processing_house.name'));

        return $formBuilder->create(ProcessingHouseForm::class)->renderForm();
    }

    public function store(ProcessingHouseRequest $request, BaseHttpResponse $response)
    {
        DB::beginTransaction();
        try {
            $processingHouse = ProcessingHouse::query()->create($request->input());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }

        event(new CreatedContentEvent(PROCESSING_HOUSE_MODULE_SCREEN_NAME, $request, $processingHouse));

        return $response
            ->setPreviousUrl(route('processing_house.index'))
            ->setNextUrl(route('processing_house.edit', $processingHouse->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ProcessingHouse $processingHouse, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::material.processing_house.edit_item', ['name' => $processingHouse->name]));

        return $formBuilder->create(ProcessingHouseForm::class, ['model' => $processingHouse])->renderForm();
    }

    public function update(ProcessingHouse $processingHouse, ProcessingHouseRequest $request, BaseHttpResponse $response)
    {
        $processingHouse->fill($request->input());

        $processingHouse->save();

        event(new UpdatedContentEvent(PROCESSING_HOUSE_MODULE_SCREEN_NAME, $request, $processingHouse));

        return $response
            ->setPreviousUrl(route('processing_house.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ProcessingHouse $processingHouse, Request $request, BaseHttpResponse $response)
    {
        try {
            $processingHouse->delete();

            event(new DeletedContentEvent(PROCESSING_HOUSE_MODULE_SCREEN_NAME, $request, $processingHouse));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
