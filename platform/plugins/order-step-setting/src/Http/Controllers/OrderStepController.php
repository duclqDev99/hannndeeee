<?php

namespace Botble\OrderStepSetting\Http\Controllers;

use Botble\OrderStepSetting\Http\Requests\OrderStepSettingRequest;
use Botble\OrderStepSetting\Models\OrderStepSetting;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\OrderStepSetting\Tables\OrderStepSettingTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\OrderStepSetting\Forms\OrderStepSettingForm;
use Botble\Base\Forms\FormBuilder;
use Botble\OrderStepSetting\Models\Action;
use Botble\OrderStepSetting\Models\Step;
use Botble\OrderStepSetting\Tables\OrderStepTable;

class OrderStepController extends BaseController
{
    public function index(OrderStepTable $table)
    {
        PageTitle::setTitle("");

        return $table->renderTable();
    }

    public function showSteps(Request $request)
    {
        $order_id = $request->order_id ?? null;

        $steps = Step::query()
            ->with(['actions' => function ($q) {
                $q->select(['id', 'title', 'step_id', 'status', 'valid_status']);
                $q->whereShow();
            }])
            ->whereHas('actions', fn ($q) => $q->whereShow())
            ->where('order_id', $order_id)
            ->get();

        return view('plugins/admin-handee-retail::steps', compact('steps'));
    }

    public function showStepDetail(Request $request)
    {
        $step_detail_id = $request->step_detail_id ?? null;
        $stepDetail = Action::with('handler:id,first_name,last_name')->find($step_detail_id);
        
        return view('plugins/admin-handee-retail::step-detail', compact('stepDetail'));
    }
}
