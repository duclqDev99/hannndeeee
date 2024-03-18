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
use Botble\OrderAnalysis\Tables\OrderQuotationTable;
use Botble\Sales\Models\Order;
use Botble\Warehouse\Models\Material;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
class OrderQuotationController extends BaseController
{
    public function index(OrderQuotationTable $table)
    {
        PageTitle::setTitle(trans('plugins/order-analysis::order-analysis.name'));

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
                        'is_canceled' => 0,
                        'reasoon' => null,
                    ];
                    
                    $quotation = OrderQuotation::query()->create($dataInsert);
                    event(new CreatedContentEvent(ORDER_QUOTATION_MODULE_SCREEN_NAME, $request, $quotation));
                }

            }catch(Exception $err){
                DB::rollBack();
                dd($err);
            }

            DB::commit();

            return $response
            ->setPreviousUrl(route('order-quotation.index'))
            ->setNextUrl(route('order-quotation.index'))
            ->setMessage('Xác nhận thành công bản báo giá cho đơn hàng!!');
        }else if($requestData['type_approved'] == 'cancel'){//Nếu admin huỷ đơn báo giá

        }
    }
}
