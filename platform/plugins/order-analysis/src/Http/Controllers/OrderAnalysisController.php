<?php

namespace Botble\OrderAnalysis\Http\Controllers;

use Botble\OrderAnalysis\Http\Requests\OrderAnalysisRequest;
use Botble\OrderAnalysis\Models\OrderAnalysis;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\OrderAnalysis\Tables\OrderAnalysisTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\OrderAnalysis\Forms\OrderAnalysisForm;
use Botble\Base\Forms\FormBuilder;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\OrderAnalysis\Models\analysisDetail;
use Botble\OrderAnalysis\Models\AnalysisProduct;
use Botble\OrderAnalysis\Models\OrderQuotation;
use Botble\OrderAnalysis\Tables\CustomOrderTable;
use Botble\Sales\Models\Order;
use Botble\Warehouse\Models\Material;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
class OrderAnalysisController extends BaseController
{
    public function index(OrderAnalysisTable $table)
    {
        PageTitle::setTitle(trans('plugins/order-analysis::order-analysis.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/order-analysis::order-analysis.create'));

        $material = Material::all(['id', 'name', 'code','unit','description']);

        return $formBuilder->create(OrderAnalysisForm::class, ['material' => $material])->renderForm();
    }

    public function store(OrderAnalysisRequest $request, BaseHttpResponse $response)
    {
        $user = auth()->guard()->user();
        $data = $request->input();
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        DB::beginTransaction();
        try{
            $orderAnalysis = OrderAnalysis::query()->create($data);
            if (empty($data['quantityAndId'])) {
                throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME: chưa chọn sản phẩm!');
            }
            foreach ($data['quantityAndId'] as $key => $val) {
                $analysisDetailData = [
                    'quantity' => $val,
                    'analysis_material_id' => $key,
                    'analysis_order_id' => $orderAnalysis->id,
                ];
                analysisDetail::query()->create($analysisDetailData);
            }
            DB::commit();
            event(new CreatedContentEvent(ORDER_ANALYSIS_MODULE_SCREEN_NAME, $request, $orderAnalysis));
            return $response
                ->setPreviousUrl(route('analyses.index'))
                ->setNextUrl(route('analyses.edit', $orderAnalysis->getKey()))
                ->setMessage(trans('core/base::notices.create_success_message'));
        }catch(Exception $e){
            throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME:' . $e);
            DB::rollBack();
        }
    }

    public function edit(OrderAnalysis $analyses, FormBuilder $formBuilder, $id)
    {
        $analyses = OrderAnalysis::find($id);
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $analyses->name]));
        $material = Material::all(['id', 'name', 'code','unit','description']);

        return $formBuilder->create(OrderAnalysisForm::class, ['model' => $analyses, 'material' => $material])->renderForm();
    }

    public function update(OrderAnalysis $orderAnalysis, OrderAnalysisRequest $request, BaseHttpResponse $response)
    {
        $user = auth()->guard()->user();
        $data = $request->input();
        $data['updated_by'] = $user->id;
        $id = $orderAnalysis->id;
        DB::beginTransaction();
        try{
            analysisDetail::where('analysis_order_id', $id)->delete();

            if(count($data['quantityAndId']) <= 0){
                throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME:' . 'chưa chọn sản phẩm!');
            }

            foreach ($data['quantityAndId'] as $key => $val) {
                $analysisDetailData = [
                    'quantity' => $val,
                    'analysis_material_id' => $key,
                    'analysis_order_id' => $orderAnalysis->id,
                ];
                analysisDetail::query()->create($analysisDetailData);
            }

            $orderAnalysis->fill($data);

            $orderAnalysis->save();

            event(new UpdatedContentEvent(ORDER_ANALYSIS_MODULE_SCREEN_NAME, $request, $orderAnalysis));

            DB::commit();

            return $response
                ->setPreviousUrl(route('analyses.index'))
                ->setMessage(trans('core/base::notices.update_success_message'));
        }catch(Exception  $e){
            DB::rollBack();
            throw new Exception('ORDER_ANALYSIS_MODULE_SCREEN_NAME:' + $e);
        }

    }

    public function destroy(OrderAnalysis $orderAnalysis, Request $request, BaseHttpResponse $response)
    {
        try {
            $orderAnalysis->delete();

            event(new DeletedContentEvent(ORDER_ANALYSIS_MODULE_SCREEN_NAME, $request, $orderAnalysis));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function orderIndex(CustomOrderTable $table)
    {
        PageTitle::setTitle(trans('plugins/sales::sales.customer.name'));

        Assets::addScriptsDirectly([
            'vendor/core/plugins/sales/js/change-step.js',
        ]);

        return $table->renderTable();
    }

    public function getOrderAnalysisApprove(string|int $idOrder)
    {
        Assets::addScriptsDirectly([
            "https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js",
            "https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        ]);
        $this->pageTitle('Bản báo giá của bộ phận');

        $order = Order::where('id', $idOrder)->first();

        return view('plugins/order-analysis::analysisProduct/quotation', compact('order'));
    }

    public function approvedOrderAnalysis(Request $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();

        //Nếu đơn được duyệt từ admin
        if($requestData['type_approved'] == 'approved'){
            //Lưu thông tin bản báo giá cuối của admin
            DB::beginTransaction();

            try{
                foreach ($requestData['quotation'] as $key => $value) {
                    # code...
                    $dataInsert = [
                        'order_id' => $requestData['order_id'],
                        'analysis_id' => $key,
                        'price' => (int) $value,
                        'status' => OrderQuotationStatusEnum::APPROVED,
                        'is_canceled' => false,
                        'reasoon' => null,
                    ];

                    $quotation = OrderQuotation::query()->create($dataInsert);
                    event(new CreatedContentEvent(ORDER_QUOTATION_MODULE_SCREEN_NAME, $request, $quotation));
                }

            }catch(Exception $err){
                DB::rollBack();
            }

            DB::commit();

            return $response
            ->setPreviousRoute(route('order-analysis.index'))
            ->setNextRoute(route('order-analysis.index'))
            ->setMessage('Xác nhận thành công bản báo giá cho đơn hàng!!');
        }else if($requestData['type_approved'] == 'cancel'){//Nếu admin huỷ đơn báo giá

        }
    }
}
