<?php

namespace Botble\Warehouse\Http\Controllers;

use Botble\Warehouse\Http\Requests\ActualReceiptRequest;
use Botble\Warehouse\Models\ActualReceipt;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Warehouse\Tables\ActualReceiptTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\ActualReceiptForm;
use Botble\Base\Forms\FormBuilder;

class ActualReceiptController extends BaseController
{
    public function index(ActualReceiptTable $table)
    {
        $this->pageTitle(trans('plugins/warehouse::actual_receipt.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::actual_receipt.create'));

        return $formBuilder->create(ActualReceiptForm::class)->renderForm();
    }

    public function store(ActualReceiptRequest $request, BaseHttpResponse $response)
    {
        $actualReceipt = ActualReceipt::query()->create($request->input());

        event(new CreatedContentEvent(ACTUAL_RECEIPT_MODULE_SCREEN_NAME, $request, $actualReceipt));

        return $response
            ->setPreviousUrl(route('actual_receipt.index'))
            ->setNextUrl(route('actual_receipt.edit', $actualReceipt->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ActualReceipt $actualReceipt, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $actualReceipt->name]));

        return $formBuilder->create(ActualReceiptForm::class, ['model' => $actualReceipt])->renderForm();
    }

    public function update(ActualReceipt $actualReceipt, ActualReceiptRequest $request, BaseHttpResponse $response)
    {
        $actualReceipt->fill($request->input());

        $actualReceipt->save();

        event(new UpdatedContentEvent(ACTUAL_RECEIPT_MODULE_SCREEN_NAME, $request, $actualReceipt));

        return $response
            ->setPreviousUrl(route('actual_receipt.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ActualReceipt $actualReceipt, Request $request, BaseHttpResponse $response)
    {
        try {
            $actualReceipt->delete();

            event(new DeletedContentEvent(ACTUAL_RECEIPT_MODULE_SCREEN_NAME, $request, $actualReceipt));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
