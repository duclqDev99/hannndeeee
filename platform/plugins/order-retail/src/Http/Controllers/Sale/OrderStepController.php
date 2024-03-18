<?php

namespace Botble\OrderRetail\Http\Controllers\Sale;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\AdminHandeeRetail\Tables\OrderTable;
use Botble\Base\Forms\FormBuilder;
use Botble\OrderRetail\Tables\OrderStepTable;
use Botble\OrderStepSetting\Models\Action;
use Botble\OrderStepSetting\Models\Step;

class OrderStepController extends BaseController
{
    public function index(OrderStepTable $table)
    {
        PageTitle::setTitle("Xem tiến độ đơn hàng");

        return $table->renderTable();
    }

    public function viewSteps(Request $request)
    {
        $order_id = $request->order_id ?? null;

        $steps = Step::query()
            ->with([
                'actions' => function ($q) {
                    $q->with('actionSetting');
                    $q->select(['id', 'step_id', 'action_code', 'status']);
                    $q->whereShow();
                },
                'stepSetting' => function ($q) {
                    $q->select(['id', 'title', 'index']);
                },
            ])
            ->whereHas('actions', fn ($q) => $q->whereShow())
            ->where('order_id', $order_id)
            ->get();

        return view('plugins/order-retail::steps', compact('steps'));
    }

    public function viewStepDetail(Request $request)
    {
        $step_detail_id = $request->step_detail_id ?? null;
        $stepDetail = Action::with(['handler:id,first_name,last_name', 'actionSetting'])->find($step_detail_id);

        return view('plugins/order-retail::step-detail', compact('stepDetail'));
    }
}
