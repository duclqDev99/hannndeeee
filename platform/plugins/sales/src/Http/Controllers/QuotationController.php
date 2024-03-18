<?php

namespace Botble\Sales\Http\Controllers;

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
use Botble\Department\Enums\OrderDepartmentStatusEnum;
use Botble\Department\Models\OrderDepartment;
use Botble\OrderAnalysis\Enums\OrderAttachStatusEnum;
use Botble\OrderAnalysis\Enums\OrderQuotationStatusEnum;
use Botble\OrderAnalysis\Http\Requests\OrderQuotationRequest;
use Botble\OrderAnalysis\Models\OrderAnalysis;
use Botble\OrderAnalysis\Models\OrderAttach;
use Botble\OrderAnalysis\Models\OrderQuatationDetail;
use Botble\OrderAnalysis\Models\OrderQuotation;
use Botble\Sales\Models\Order;
use Botble\Sales\Tables\OrderQuotationTable;
use Botble\Warehouse\Models\Material;
use Illuminate\Support\Facades\DB;

class QuotationController extends BaseController
{
    public function index(OrderQuotationTable $table)
    {
        PageTitle::setTitle(trans('plugins/order-analysis::order-analysis.name'));

        return $table->renderTable();
    }

    public function create(Order $order)
    {
        //Kiểm tra đơn hàng hiện tại đã có bản báo giá chưa
        //Nếu có thì trả về 404
        abort_if(!empty($order->quotation), 404);

        Assets::addScriptsDirectly([
            "https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js",
            "https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        ]);
        $this->pageTitle('Tạo bản báo giá');

        return view('plugins/sales::orders/quotation/quotation', compact('order'));
    }

    public function store(OrderQuotationRequest $request, BaseHttpResponse $response)
    {
        $requestData = $request->input();
        $totalAmount = 0;

        DB::beginTransaction();

        try{
            foreach ($requestData['quotation'] as $key => $value) {
                $totalAmount += (int) $value;
            }
    
            $dataInsert = [
                'order_id' => $requestData['order_id'],
                'title' => $requestData['title'],
                'total_amount' => $totalAmount,
                'effective_time' => $requestData['effective_time'],
                'effective_payment' => $requestData['effective_payment'],
                'transport_costs' => $requestData['transport_costs'],
                'status' => OrderQuotationStatusEnum::CREATED,
                'is_canceled' => 0,
                'reasoon' => '',
                'description' => $requestData['description'],
            ];
    
            $quotation = OrderQuotation::query()->create($dataInsert);
            event(new CreatedContentEvent(ORDER_QUOTATION_MODULE_SCREEN_NAME, $request, $quotation));

            /**
             * Cập nhật trạng thái của file attach của đơn
             * Lấy tất cả file thiết kế của đơn hàng hiện tại
             */
            foreach ($requestData['attach'] as $key => $value) {
                OrderAttach::where(['id' => (int) $value,])->first()->update(['status' => OrderAttachStatusEnum::APPROVED]);

                //Ghi chi tiết cho đơn báo giá
                OrderQuatationDetail::query()->create([
                    'quotation_id' => $quotation->id,
                    'analysis_detail_id' => $key,
                    'price' => $requestData['quotation'][$key],
                ]);
            }

            //Cập nhật trạng thái của bộ phận
            $order = Order::where('id', $requestData['order_id'])->first();

            if(!empty($order)){
                /**
                 * TODO: Lấy mã code của bộ phận hiện tại
                 */
                $departmentCode = 'admin_001';
                $orderRelationship = $order->orderDepartments->where('department_code', $departmentCode)->first();
                $orderRelationship->update(['status' => OrderDepartmentStatusEnum::COMPLETED]);
            }else{
                DB::rollBack();
                return $response->setError()->setMessage('Không tìm thấy đơn hàng này!!');
            }

            DB::commit();

            return $response
                ->setNextUrl(route('order-analyses.index'))
                ->setMessage('Tạo phiếu báo giá thành công!!');
        }catch(Exception $err){
            DB::rollBack();
            return $response->setError()->setMessage($err->getMessage());
        }
    }

    public function cancelAttach(Request $request){
        dd($request->input());
    }
}
