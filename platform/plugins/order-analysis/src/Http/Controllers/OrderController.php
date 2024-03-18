<?php
namespace Botble\OrderAnalysis\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Department\Models\OrderDepartment;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\OrderAnalysis\Tables\CustomOrderTable;
use Botble\Sales\Models\Order;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\OrderAnalysis\Http\Requests\EditOrderAnalysisRequest;
use Botble\OrderAnalysis\Models\OrderAnalysis;
use Botble\OrderAnalysis\Models\OrderAttach;
use Botble\ProcedureOrder\Models\ProcedureOrder;
use Botble\Sales\Models\OrderHistory;
use Botble\Sales\Models\OrderReferenceProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
use Carbon\Carbon;

class OrderController extends BaseController
{
    public function __construct(
        protected HandleShippingFeeService $shippingFeeService,
        protected HandleApplyCouponService $handleApplyCouponService,
        protected HandleApplyPromotionsService $applyPromotionsService
    ) {

        $this
            ->breadcrumb()
            ->add(trans('plugins/sales::orders.menu'), route('purchase-order.index'));
    }

    public function index(CustomOrderTable $table)
    {
        PageTitle::setTitle(trans('plugins/order-analysis::order-analysis.orders.title-page-index'));


        Assets::addScriptsDirectly([
            'vendor/core/plugins/sales/js/change-step.js',
        ]);

        return $table->renderTable();

    }
    public function edit(FormBuilder $formBuilder,$id)
    {
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
        ->addScriptsDirectly([
            'vendor/core/plugins/ecommerce/libraries/jquery.textarea_autosize.js',
            'vendor/core/plugins/ecommerce/js/order.js',
        ])
        ->addScripts(['input-mask']);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }
        $order = Order::find($id);
        $orderAnalysis = OrderAnalysis::all();
        $order->load(['order_detail']);

        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $order->order_code]));
        return view('plugins/order-analysis::orders.edit', compact('order', 'orderAnalysis'));
    }

    public function update(EditOrderAnalysisRequest $request, BaseHttpResponse $response, $id){
        $submitEdit = isset($request->input()['editAnalyses']);
        $submitSuccess = isset($request->input()['successAnalyses']);
        $submitReject = isset($request->input()['rejectAnalyses']);
        $submitCompleted = isset($request->input()['completedAnalyses']);
        $dataOrderDepartment = [];
        $depart_code_current = 'r_01'; //Mã của bộ phận hiện tại
        $getProcedure = ProcedureOrder::where('department_code', $depart_code_current)->first();
        /////////////////////////////////////////////
        $orderDepartment = OrderDepartment::where('order_id', $id)->where('department_code', $depart_code_current)->first();
        $statusOrderDepartment = 'editing';
        $messageSuccess = 'Chỉnh sửa thành công!!!';
        if($submitSuccess || $submitCompleted){
            switch ($orderDepartment->status) {
                case 'waiting':
                    $statusOrderDepartment = 'approved';
                    $messageSuccess = 'Nhận đơn thành công!!!';
                    break;
                case 'approved':
                    $statusOrderDepartment = 'processing';
                    $messageSuccess = 'Gửi bản thiết kế thành công!!!';
                    break;
                case 'reject':
                    $statusOrderDepartment = 'processing';
                    $messageSuccess = 'Gửi lại bản thiết kế thành công!!!';
                    break;
                case 'processing':
                    $statusOrderDepartment = 'completed';
                    $messageSuccess = 'Hoàn thành bản thiết kế!!!';
                    break;
                case 'completed':

                    break;
                default:
                    # code...
                    break;
            }
        }
        if($submitReject){
            $statusOrderDepartment = 'reject';
            $messageSuccess = 'Từ chối bản thiết kế thành công!!!';
        }
        DB::beginTransaction();
        try{
            $order = Order::find($id);
            $getProcedure = ProcedureOrder::where('code', $depart_code_current)->first();

            if($submitEdit){
                OrderAttach::where('order_id', $id)->update([
                    'attach_id' => $request->input()['analysis_id']
                ]);

                $orderDepartment->update([
                    'expected_date' => $request->input()['expected_date'] ,
                    'assignee_id' => Auth::user()->id,
                ]);
                $orderDepartment->save();
            }
            if($submitSuccess || $submitCompleted){
                if($orderDepartment->status == 'waiting'){ //cập nhật bước ở trạngt thái chờ xác nhận
                    $dataOrderDepartment = [
                        'status' => $statusOrderDepartment,
                        'expected_date' => $request->input()['expected_date'],
                        'assignee_id' => Auth::user()->id,
                    ];
                }
                if($orderDepartment->status == 'approved'){ //cập nhật bước ở trạngt thái xác nhận
                    $dataOrderDepartment = [
                        'status' => $statusOrderDepartment,
                        'assignee_id' => Auth::user()->id,
                    ];
                    $orderAttach = OrderAttach::query()->create([
                        'order_id' => $id,
                        'attach_type' => OrderAnalysis::class,
                        'attach_id' => $request->input()['analysis_id']
                    ]);
                    $orderAttach->save();
                    event(new CreatedContentEvent(ORDER_ANALYSIS_MODULE_SCREEN_NAME, $request, $orderAttach));
                }
                if($orderDepartment->status == 'reject'){
                    $dataOrderDepartment = [
                        'status' => $statusOrderDepartment,
                        'assignee_id' => Auth::user()->id,
                    ];
                    $attachType = OrderAnalysis::class;
                    OrderAttach::where('order_id', $id)
                        ->where('attach_type', $attachType)
                        ->update(['attach_id' => $request->input()['analysis_id']]);
                }
                if($orderDepartment->status == 'processing'){ //cập nhật bước ở trạngt thái đang xử lý
                    $dataOrderDepartment = [
                        'status' => $statusOrderDepartment,
                        'assignee_id' => Auth::user()->id,
                        'completion_date' => Carbon::now(),
                    ];
                }

                $orderDepartment->update($dataOrderDepartment);
                $orderDepartment->save();
            }

            if($submitReject){
                $orderDepartment->update([
                    'status' => $statusOrderDepartment,
                    'assignee_id' => Auth::user()->id,
                ]);
                $orderDepartment->save();
            }

            if($submitCompleted){
                $orderDepartment->update([
                    'status' => $statusOrderDepartment,
                    'assignee_id' => Auth::user()->id,
                ]);
                $orderDepartment->save();
                //chuyển bước cho đơn hàng
                $this->checkNextStep($getProcedure, $depart_code_current, $order_id = $id);
            }

            $orderHistory = OrderHistory::query()->create([
                'order_id' => $order->id,
                'procedure_code_previous' => $depart_code_current,
                'procedure_name_previous' => $getProcedure->name,
                'procedure_code_current' => $depart_code_current,
                'status' => $statusOrderDepartment,
                'procedure_name_current' => $getProcedure->name,
                'created_by' => Auth::user()->id,
                'created_by_name' => Auth::user()->name,
                'description' => $request->input()['descriptionForm'],
            ]);
            // $orderHistory->save();
            DB::commit();
            event(new CreatedContentEvent(ORDER_ANALYSIS_MODULE_SCREEN_NAME, $request, $orderHistory));

            return $this
                ->httpResponse()
                ->setMessage($messageSuccess);
        }catch(Throwable $exception){
            dd($exception);
            DB::rollBack();
            BaseHelper::logError($exception);
        }
    }

    public function checkNextStep($getProcedure, $depart_code_current, $order_id){
        $checkConditionsNextStep = true;
        if(isset($getProcedure?->next_step)){
            foreach ($getProcedure->next_step as $keyNextStep=>$nextStep) {
                foreach ($nextStep as $key=>$val) {
                    $checkExit = OrderDepartment::where('order_id', $order_id)
                        ->where('department_code', $key)
                        ->where('status', $val)
                        ->exists();
                    if(!$checkExit){
                        $checkConditionsNextStep = false;
                    }
                }
                if($checkConditionsNextStep){
                    $_nextStep[] = $keyNextStep;
                }
            }
        }
        if(isset($_nextStep)){
            foreach ($_nextStep as $val) {
                OrderDepartment::insert([
                    'order_id' => $order_id,
                    'department_code' => $val,
                    'assignee_id' => Auth::user()->id,
                    'status' => 'waiting',
                ]);
                OrderReferenceProcedure::create([
                    'order_id' => $order_id,
                    'procedure_code' => $val,
                ]);
            }
        }
        OrderReferenceProcedure::where('order_id', $order_id)->where('procedure_code', $depart_code_current)->delete();
    }

    public function postConfirm(Order $order, Request $request)
    {
        abort_if(empty($order), 403);

        if($order->status != OrderStatusEnum::PENDING){
            return $this
            ->httpResponse()
            ->setError()
            ->setMessage('Đơn này đã được tiến hành hoặc bị huỷ. Không thể duyệt được!!');
        }

        // $pre
        $statusPreUpdate = $order->procedure_code;

        $arrStatusProcess = [];
        $arrNameProcess = [];

        $depart_code_current = 'td_01'; //Mã của bộ phận hiện tại
        $getProcedure = ProcedureOrder::where('code', $depart_code_current)->first();

        $statusCurrentDepartment = OrderDepartment::where(['order_id' => $order->id, 'department_code' => $depart_code_current])->first()?->status;

        if(!empty($order->procedures)){
            // Kiểm tra nếu giải mã thành công
            foreach ($order->procedures as $key => $item) {//Lấy từng giá trị trong mảng các bước tiếp theo
                if($item->code == $getProcedure->code)
                {
                    foreach ($getProcedure->next_step as $keyStep => $subArray) {//Lấy từng điều kiện trong quy trình
                        //Kiểm tra key có tồn tại trong mảng hay không

                        if (array_key_exists($getProcedure->code, $subArray)) {
                            if($statusCurrentDepartment == $subArray[$getProcedure->code])
                            {
                                $procedureNext = ProcedureOrder::where('code', $keyStep)->first();

                                if(!empty($procedureNext)){
                                    $arrStatusProcess[$procedureNext->code] = 'waiting';
                                    $arrNameProcess[$procedureNext->code] = $procedureNext->name;
                                }
                            }
                        }
                    }
                }
            }
        }

        $order->status = OrderStatusEnum::PROCESSING;
        $order->procedure_code = ($arrStatusProcess);//Cập nhật vị trí hiện tại của đơn hàng
        $order->save();
        event(new UpdatedContentEvent(CREATE_PRODUCT_WHEN_ORDER_MODULE_SCREEN_NAME, $request, $order));

        //Lưu lại lịch sử đơn hàng
        foreach ($arrStatusProcess as $key => $item) {
            # code...
            $orderHistory = OrderHistory::query()->create([
                'order_id' => $order->id,
                'procedure_code_previous' => $key,
                'procedure_name_previous' => $getProcedure->name,
                'procedure_code_current' => $item,
                'procedure_name_current' => $arrNameProcess[$key],
                'created_by' => Auth::user()->id,
                'created_by_name' => Auth::user()->name,
                'status' => $order->status,
                'description' => $order->status,
            ]);
            event(new CreatedContentEvent(HISTORY_ORDER_MODULE_SCREEN_NAME, $request, $order));
        }

        //Import

        return $this
        ->httpResponse()
        ->withCreatedSuccessMessage();
    }
}
