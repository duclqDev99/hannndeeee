<?php

namespace Botble\OrderHgf\Http\Controllers\Admin;

use Botble\Sales\Http\Requests\SalesRequest;
use Botble\Sales\Models\Sales;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Sales\Forms\SalesForm;
use Botble\Base\Forms\FormBuilder;
use Botble\OrderHgf\Tables\Admin\PurchaseOrderTable;
use Botble\OrderRetail\Models\Order;
use Botble\Base\Facades\Assets;
use Botble\OrderRetail\Models\OrderProduct;
use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\OrderStepSetting\Enums\ActionStatusEnum;
use Botble\OrderStepSetting\Services\StepService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends BaseController
{
    public function index(PurchaseOrderTable $table)
    {
        PageTitle::setTitle(trans('HGF | Yêu cầu sản xuất'));

        return $table->renderTable();
    }

    public function show(Order $order)
    {
        abort_if(!$order, 404, 'page not found');
        Assets::addStylesDirectly(['vendor/core/plugins/ecommerce/css/ecommerce.css'])
            ->addScriptsDirectly([
                'vendor/core/plugins/order-retail/js/edit-product.js',
            ])
            ->addScripts(['input-mask']);

        return view('plugins/order-hgf::admin.purchase-order.show', compact('order'));
    }

    public function confirm(Request $request, StepService $stepService)
    {
        $type = $request->type ?? null;
        $result = $this->httpResponse();
        DB::beginTransaction();
        try {
            if ($type == 'cancel') {
                $products = $request->products;
                foreach ($products as $id => $priceEdit) {
                    OrderProduct::find($id)->update(['hgf_price' => $priceEdit]);
                }
                $stepService->updateStep(ActionEnum::HGF_ADMIN_CONFIRM_ORDER, [
                    'order_id' => $request->order_id,
                    'status' => ActionStatusEnum::CANCELED,
                    'note' => NULL,
                    'type' => 'prev',
                ]);
                $result->setMessage('Đã đề xuất chỉnh sửa yêu cầu sản xuất');
            }

            if ($type == 'confirm') {
                $stepService->updateStep(ActionEnum::HGF_ADMIN_CONFIRM_ORDER, [
                    'order_id' => $request->order_id,
                    'status' => ActionStatusEnum::CONFIRMED,
                    'note' => NULL,
                    'type' => 'next',
                ]);
                $result->setMessage('Đã xác nhận yêu cầu sản xuất');
            }
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return $result->setError()->setMessage($e->getMessage());
        }
    }
}
