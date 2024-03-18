<?php

namespace Botble\QrScan\Http\Controllers;

use Botble\QrScan\Http\Requests\QrScanRequest;
use Botble\QrScan\Models\QrScan;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\QrScan\Tables\QrScanTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\QrScan\Forms\QrScanForm;
use Botble\Base\Forms\FormBuilder;

class QrScanController extends BaseController
{
    public function index(QrScanTable $table)
    {
        PageTitle::setTitle(trans('plugins/qr-scan::qr-scan.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/qr-scan::qr-scan.create'));

        return $formBuilder->create(QrScanForm::class)->renderForm();
    }

    public function store(QrScanRequest $request, BaseHttpResponse $response)
    {
        $qrScan = QrScan::query()->create($request->input());

        event(new CreatedContentEvent(QR_SCAN_MODULE_SCREEN_NAME, $request, $qrScan));

        return $response
            ->setPreviousUrl(route('qr-scan.index'))
            ->setNextUrl(route('qr-scan.edit', $qrScan->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(QrScan $qrScan, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $qrScan->name]));

        return $formBuilder->create(QrScanForm::class, ['model' => $qrScan])->renderForm();
    }

    public function update(QrScan $qrScan, QrScanRequest $request, BaseHttpResponse $response)
    {
        $qrScan->fill($request->input());

        $qrScan->save();

        event(new UpdatedContentEvent(QR_SCAN_MODULE_SCREEN_NAME, $request, $qrScan));

        return $response
            ->setPreviousUrl(route('qr-scan.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(QrScan $qrScan, Request $request, BaseHttpResponse $response)
    {
        try {
            $qrScan->delete();

            event(new DeletedContentEvent(QR_SCAN_MODULE_SCREEN_NAME, $request, $qrScan));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
