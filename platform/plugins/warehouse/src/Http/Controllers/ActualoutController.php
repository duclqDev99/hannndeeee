<?php

namespace Botble\Warehouse\Http\Controllers;

use Auth;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Http\Requests\ActualoutRequest;
use Botble\Warehouse\Models\ActualOut;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Warehouse\Models\DetailBatchMaterial;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Botble\Warehouse\Tables\ActualOutTable;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Warehouse\Forms\ActualoutForm;
use Botble\Base\Forms\FormBuilder;
use DB;
use Illuminate\Support\Carbon;

class ActualOutController extends BaseController
{
    public function index(ActualOutTable $table)
    {
        $this->pageTitle(trans('plugins/warehouse::actualout.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/warehouse::actualout.create'));

        return $formBuilder->create(ActualoutForm::class)->renderForm();
    }

    public function store(ActualoutRequest $request, BaseHttpResponse $response)
    {
        $actualout = ActualOut::query()->create($request->input());

        event(new CreatedContentEvent(ACTUALOUT_MODULE_SCREEN_NAME, $request, $actualout));

        return $response
            ->setPreviousUrl(route('actualout.material-out-confirm.index'))
            ->setNextUrl(route('actualout.edit', $actualout->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(ActualOut $actualOut, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $actualOut->name]));

        return $formBuilder->create(ActualoutForm::class, ['model' => $actualOut])->renderForm();
    }

    public function update(ActualOut $actualOut, ActualoutRequest $request, BaseHttpResponse $response)
    {
        $actualOut->fill($request->input());

        $actualOut->save();

        event(new UpdatedContentEvent(actualOut_MODULE_SCREEN_NAME, $request, $actualOut));

        return $response
            ->setPreviousUrl(route('actualout.material-out-confirm.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ActualOut $actualOut, Request $request, BaseHttpResponse $response)
    {
        try {
            $actualOut->delete();

            event(new DeletedContentEvent(ACTUALOUT_MODULE_SCREEN_NAME, $request, $actualOut));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function confirmReceipt(int|string $id)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
                'vendor/core/plugins/ecommerce/js/order.js',
                'vendor/core/plugins/warehouse/js/receipt.js',
            ])
            ->addScripts(['blockui', 'input-mask']);

        $receipt = Actualout::where(['id' => $id])->with('receiptDetail')->first();

        $this->pageTitle(__('Xác nhận nhập kho'));

        return view('plugins/warehouse::material.receipt.receipt-out-details', compact('receipt'));
    }
    public function storeConfirmReceipt(int|string $id, Request $request, BaseHttpResponse $response)
    {
        $actualOut = ActualOut::where(['id' => $id])->first();
        $requestData = $request->input();

        DB::beginTransaction();
        try {
            $actualOut->update([
                'status' => MaterialProposalStatusEnum::APPOROVED,
                'invoice_confirm_name' => Auth::user()->name,
                'date_confirm' => Carbon::now()->format('Y-m-d'),
            ]);

            foreach ($requestData['material'] as $key => $value) {
                $quantity = $value['quantity'] ?? null;
                DetailBatchMaterial::where('id', $key)
                    ->update(['quantity_actual' => $quantity]);
                $batch_code = $value['batch_code'];
                $batch = MaterialBatch::where('batch_code', $batch_code)->first();
                if ((int) $batch->quantity - (int) $quantity < 0) {
                    return $response
                    ->setNextUrl(route('actualout.material-out-confirm.index'))
                        ->setError()
                        ->setMessage((int) $batch->quantity - (int) $quantity);
                }
                $batch->update(['quantity' => (int) $batch->quantity - (int) $quantity]);
                $quantityStock = QuantityMaterialStock::where('warehouse_id', $requestData['warehouse_id'])->where('material_id', $batch->material_id)->first();
                $quantityStock->update(['quantity' => (int) $quantityStock->quantity - (int) $quantity]);

            }

            DB::commit();
            return $response
                ->setNextUrl(route('actualout.material-out-confirm.index'))
                ->setMessage(trans('core/base::notices.create_success_message'));
        } catch (\Throwable $e) {
            dd($e);
            DB::rollBack();


        }
    }
}
