<?php

namespace Botble\OrderStepSetting\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Location\Enums\ImportType;
use Botble\Location\Events\ImportedCityEvent;
use Botble\Location\Events\ImportedCountryEvent;
use Botble\Location\Events\ImportedStateEvent;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Botble\OrderHgf\Noti\HGFNoti;
use Botble\OrderRetail\Models\Order;
use Botble\OrderRetail\Noti\RetailNoti;
use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\OrderStepSetting\Enums\ActionStatusEnum;
use Botble\OrderStepSetting\Models\Action;
use Botble\OrderStepSetting\Models\Step;
use Botble\OrderStepSetting\Models\StepSetting;
use Botble\Sales\Enums\StepActionEnum;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Break_;

class StepService
{
    public function init(BaseModel|QueryBuilder $order)
    {
        $stepSettings = StepSetting::with('actionSettings')->where('is_init', true)->get();

        $stepSettings->each(function ($stepSetting, $index) use ($order) {
            $step = Step::create([
                'step_index' => $stepSetting->index,
                'order_id' => $order->id,
                'is_ready' => $index == 0 ? true : false,
            ]);
            $stepSetting->actionSettings->each(function ($actionSetting) use ($step) {
                $data = [
                    'step_id' => $step->id,
                    'action_code' => $actionSetting->action_code,
                    'status' => ActionStatusEnum::NOT_READY
                ];
                Action::create($data);
            });
        });
    }

    public function updateStep($action_code, $data)
    {
        $order_id = Arr::get($data, 'order_id');
        $status = Arr::get($data, 'status');
        $note = Arr::get($data, 'note');
        $type = Arr::get($data, 'type'); //next or prev

        DB::beginTransaction();
        try {
            $stepDetail = get_action($action_code, $order_id);
            $stepDetail->update([
                "status" => $status,
                "note" => $note ?? null,
                "handler_id" => auth()->user()->id,
                "handled_at" => now(),
            ]);

            $step = $stepDetail->step;
            if ($step && $step->is_completed) {
                $nextStep = Step::query()
                    ->where('order_id', $order_id)
                    ->where('step_index', $step->step_index + 1)
                    ->first();

                if ($nextStep) {
                    $nextStep->update(['is_ready' => true]);
                }
            }

            $relates = $stepDetail?->actionSetting?->update_relate_actions;
            if ($relates) {
                foreach ($relates as $action_type => $actions) {
                    if ($action_type == $type && is_array($actions)) {
                        foreach ($actions as $action_code => $status) {
                            $step = get_action($action_code, $order_id);
                            $step->update(['status' => $status]);
                        }
                    }
                }
            }
            DB::commit();
            $this->sendNotiAfterUpdate($stepDetail, $order_id);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    protected function sendNotiAfterUpdate($action, $order_id)
    {
        $order = Order::find($order_id);
        $action_code = $action->action_code ?? null;

        switch ($action_code) {
            case ActionEnum::RETAIL_SALE_REQUESTING_APPROVE_ORDER:
                $arrNoti = [
                    'title' => 'Có 1 yêu cầu sản xuất cần phê duyệt',
                    'type_name' => 'Số YCSX',
                    'type_value' => $order->code,
                    'customer_name' => $order->customer_name,
                    'customer_phone' =>  $order->customer_phone,
                    'route' => route('retail.sale.purchase-order.show', $order_id),
                    'permission' => 'retail.sale.purchase-order.index',
                    'action' => 'Gửi phê duyệt YCSX cho Sale Admin',
                    'note' => $action->note ?? ''
                ];
                send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                break;

            case ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_ORDER:
                if ($action->status == 'confirmed') {
                    $arrNoti = [
                        'title' => 'Sale Admin đã duyệt yêu cầu sản xuất',
                        'type_name' => 'Số YCSX',
                        'type_value' => $order->code,
                        'customer_name' => $order->customer_name,
                        'customer_phone' =>  $order->customer_phone,
                        'route' => route('retail.sale.purchase-order.show', $order_id),
                        'permission' => 'retail.sale.purchase-order.index',
                        'action' => 'Duyệt yêu cầu sản xuất',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);

                    // send hgf
                    $arrNoti['title'] = 'Có 1 yêu cầu sản xuất cần phê duyệt';
                    $arrNoti['route'] = route('hgf.admin.purchase-order.show', $order_id);
                    $arrNoti['action'] = 'Gửi phê duyệt YCSX cho HGF';
                    $arrNoti['note'] = $action->note ?? '';
                    send_notify_cms_and_tele(new HGFNoti, $arrNoti);
                }

                if ($action->status == 'canceled') {
                    $arrNoti = [
                        'title' => 'Sale Admin đã từ chối yêu cầu sản xuất',
                        'type_name' => 'Số YCSX',
                        'type_value' => $order->code,
                        'customer_name' => $order->customer_name,
                        'customer_phone' =>  $order->customer_phone,
                        'route' => route('retail.sale.purchase-order.show', $order_id),
                        'permission' => 'retail.sale.purchase-order.index',
                        'action' => 'Từ chối yêu cầu sản xuất',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                }
                break;

            case ActionEnum::HGF_ADMIN_CONFIRM_ORDER:
                if ($action->status == 'confirmed') {
                    $arrNoti = [
                        'title' => '1 yêu cầu sản xuất được duyệt',
                        'type_name' => 'Số YCSX',
                        'type_value' => $order->code,
                        'customer_name' => $order->customer_name,
                        'customer_phone' =>  $order->customer_phone,
                        'route' => route('hgf.admin.purchase-order.show', $order_id),
                        'permission' => 'hgf.admin.purchase-order.index',
                        'action' => 'Duyệt yêu cầu sản xuất',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new HGFNoti, $arrNoti);

                    // send retail
                    $arrNoti['title'] = 'HGF đã duyệt yêu cầu sản xuất';
                    $arrNoti['route'] = route('retail.sale.purchase-order.show', $order_id);
                    $arrNoti['note'] = $action->note ?? '';
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                }

                if ($action->status == 'canceled') {
                    $arrNoti = [
                        'title' => 'HGF đã từ chối yêu cầu sản xuất',
                        'type_name' => 'Số YCSX',
                        'type_value' => $order->code,
                        'customer_name' => $order->customer_name,
                        'customer_phone' =>  $order->customer_phone,
                        'route' => route('hgf.admin.purchase-order.show', $order_id),
                        'permission' => 'hgf.admin.purchase-order.index',
                        'action' => 'Từ chối YCSX',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                }
                break;

            case ActionEnum::RETAIL_SEND_QUOTATION:
                $arrNoti = [
                    'title' => 'Có 1 báo giá cần phê duyệt',
                    'type_name' => 'Báo giá',
                    'type_value' => $order->quotation->title,
                    'customer_name' => $order->customer_name,
                    'customer_phone' =>  $order->customer_phone,
                    'route' => route('retail.sale.quotation.show', $order->quotation->id),
                    'permission' => 'retail.sale.quotation.index',
                    'action' => 'Gửi phê duyệt báo giá',
                    'note' => $action->note ?? ''
                ];
                send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                break;

            case ActionEnum::RETAIL_SALE_MANAGER_CONFIRM_QUOTATION:
                if ($action->status == 'confirmed') {
                    $arrNoti = [
                        'title' => 'Sale Admin đã phê duyệt báo giá',
                        'type_name' => 'Báo giá',
                        'type_value' => $order->quotation->title ?? '',
                        'customer_name' => $order->customer_name,
                        'customer_phone' =>  $order->customer_phone,
                        'route' => route('retail.sale.quotation.show', $order->quotation->id),
                        'permission' => 'retail.sale.quotation.index',
                        'action' => 'Duyệt báo giá',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                }

                if ($action->status == 'canceled') {
                    $arrNoti = [
                        'title' => 'Sale Admin đã từ chối báo giá',
                        'type_name' => 'Báo giá',
                        'type_value' => $order->quotation->title ?? '',
                        'customer_phone' =>  $order->customer_phone,
                        'customer_name' => $order->customer_name,
                        'route' => route('retail.sale.quotation.show', $order->quotation->id),
                        'permission' => 'retail.sale.quotation.index',
                        'action' => 'Từ chối báo giá',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                }
                break;

            case ActionEnum::CUSTOMER_CONFIRM_QUOTATION:
                $arrNoti = [
                    'title' => 'Xác nhận Khách hàng đồng ý báo giá',
                    'type_name' => 'Báo giá',
                    'type_value' => $order->quotation->title,
                    'customer_name' => $order->customer_name,
                    'customer_phone' =>  $order->customer_phone,
                    'route' => route('retail.sale.quotation.show', $order->quotation->id),
                    'permission' => 'retail.sale.quotation.index',
                    'action' => 'xác nhận khách hàng đồng ý báo giá',
                    'note' => $action->note ?? ''
                ];
                send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                break;

            case ActionEnum::CUSTOMER_SIGN_CONTRACT:
                $arrNoti = [
                    'title' => 'Xác nhận khách hàng ký hợp đồng',
                    'type_name' => 'Báo giá',
                    'type_value' => $order->quotation->title,
                    'customer_name' => $order->customer_name,
                    'customer_phone' =>  $order->customer_phone,
                    'route' => route('retail.sale.quotation.show', $order->quotation->id),
                    'permission' => 'retail.sale.quotation.index',
                    'action' => 'Kí hợp đồng với khách hàng',
                    'note' => $action->note ?? ''
                ];
                send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                break;

            case ActionEnum::CUSTOMER_DEPOSIT:
                $arrNoti = [
                    'title' => 'Kế toán xác nhận khách hàng đã cọc tiền',
                    'type_name' => 'Báo giá',
                    'type_value' => $order->quotation->title,
                    'customer_name' => $order->customer_name,
                    'customer_phone' =>  $order->customer_phone,
                    'route' => route('retail.sale.quotation.show', $order->quotation->id),
                    'permission' => 'retail.sale.quotation.index',
                    'action' => 'xác nhận khách cọc tiền',
                    'note' => $action->note ?? ''
                ];
                send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                break;

            case ActionEnum::RETAIL_SALE_SEND_PRODUCTION:
                $arrNoti = [
                    'title' => 'Gửi yêu cầu phê duyệt đơn đặt hàng cho HGF',
                    'type_name' => 'Đơn đặt hàng',
                    'type_value' => $order->production->code,
                    'customer_name' => $order->customer_name,
                    'customer_phone' =>  $order->customer_phone,
                    'route' => route('hgf.admin.production.show', $order->production->id),
                    'permission' => 'hgf.admin.production.index',
                    'action' => 'Gửi yêu cầu phê duyệt',
                    'note' => $action->note ?? ''
                ];
                send_notify_cms_and_tele(new HGFNoti, $arrNoti);
                break;

            case ActionEnum::HGF_ADMIN_CONFIRM_PRODUCTION:
                if ($action->status == 'confirmed') {
                    $arrNoti = [
                        'title' => 'HGF xác nhận sản xuất',
                        'type_name' => 'Đơn đặt hàng',
                        'type_value' => $order->production->code,
                        'customer_name' => $order->customer_name,
                        'customer_phone' =>  $order->customer_phone,
                        'route' => route('retail.sale.production.show', $order->production->id),
                        'permission' => 'retail.sale.production.index',
                        'action' => 'Xác nhận sản xuất',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);

                    // send hgf
                    $arrNoti['route'] = route('hgf.admin.production.show', $order->production->id);
                    $arrNoti['note'] = $action->note ?? '';
                    send_notify_cms_and_tele(new HGFNoti, $arrNoti);
                }

                if ($action->status == 'canceled') {
                    $arrNoti = [
                        'title' => 'HGF đã từ chối sản xuất',
                        'type_name' => 'Đơn đặt hàng',
                        'type_value' => $order->production->code,
                        'customer_phone' =>  $order->customer_phone,
                        'customer_name' => $order->customer_name,
                        'route' => route('retail.sale.quotation.show', $order->quotation->id),
                        'permission' => 'retail.sale.quotation.index',
                        'action' => 'Từ chối đơn đặt hàng',
                        'note' => $action->note ?? ''
                    ];
                    send_notify_cms_and_tele(new RetailNoti, $arrNoti);
                }
                break;
        }
    }
}
