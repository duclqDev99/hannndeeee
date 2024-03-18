<?php

namespace Botble\OrderHgf\Tables\Admin;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sales\Models\Customer;
use Botble\OrderRetail\Models\Order;
use Botble\OrderRetail\Models\OrderProduction;
use Botble\OrderRetail\Models\OrderQuotation;
use Botble\OrderStepSetting\Enums\ActionEnum;
use Botble\OrderStepSetting\Enums\ActionStatusEnum;
use Botble\Sales\Models\Sales;
use Botble\Sales\Tables\Actions\NextStepAction;
use Botble\Sales\Tables\Actions\PrevStepAction;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Sales\Actions\DeleteAction;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Enums\OrderStepStatusEnum;
use Botble\Sales\Enums\StepActionEnum;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ProductionTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(OrderProduction::class)
            ->addActions([
                EditAction::make()
                    ->route('purchase-order.edit'),
                DeleteAction::make()
                    ->route('purchase-order.destroy'),
            ])
            ->removeAllActions()
            ->setView('plugins/order-retail::table.index');
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('order_code', function (OrderProduction $item) {
                $paramCode = str_replace('#', '',  $item->order_code);
                return Html::link(route('hgf.admin.purchase-order.index') . '?order_code=' . $paramCode, $item->order_code);
            })
            ->editColumn('customer_name', function (OrderProduction $item) {
                return $item->order->customer_name;
            })
            ->editColumn('customer_confirm', function (OrderProduction $item) {
                return view('plugins/order-retail::sale.quotation.customer-confirm', compact('item'));
            })
            ->editColumn('status', function (OrderProduction $item) {
                return 'Processing';
            })
            ->editColumn('payment_status', function (OrderProduction $item) {
                return 'Chưa cọc tiền';
            })
            ->editColumn('amount', function (OrderProduction $item) {
                return  number_format($item->order->amount, 0, ',', '.') . '₫';
            })
            ->editColumn('expected_date', function (OrderProduction $item) {
                return  Carbon::createFromDate($item->order->expected_date)->format('d-m-Y');
            })
            ->editColumn('sended_by', function (OrderProduction $item) {
                $created = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_SEND_PRODUCTION, $item->order->id);
                if ($created) {
                    $created->load('handler:id,first_name,last_name');
                }
                return $created?->handler?->name;
            })
            ->editColumn('confirm_by', function (OrderProduction $item) {
                $created = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::HGF_ADMIN_CONFIRM_PRODUCTION, $item->order->id);
                if ($created) {
                    $created->load('handler:id,first_name,last_name');
                }
                return $created?->handler?->name;
            })
            ->editColumn('status', function (OrderProduction $item) {
                return $item->order->lastAction->actionSetting->title;
            })
            ->editColumn('operators', function (OrderProduction $item) {
                return view('plugins/order-hgf::admin.production.dropdown-actions', compact('item'));
            });
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        // $department_code = get_department_code_curr_user()[0]->department_code;
        $department_code = 'hgf_admin';
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'code',
                'order_code',
                'quotation_id',
                'status',
                'note',
                'created_by_id',
            ])
            ->with(['order:id,code,customer_name,amount'])
            ->whereHas('order.steps', function ($q) use ($department_code) {
                $q->where('is_ready', true);
                $q->whereHas('actions', function ($q) use ($department_code) {
                    $q->where('status', '!=', ActionStatusEnum::NOT_READY);
                    $q->whereRelation('actionSetting', 'hd_action_setting.department_code', $department_code);
                    $q->whereRelation('actionSetting', 'hd_action_setting.action_type', 'production_order');
                });
            })
            ->with([
                'order.lastAction'
            ])
            ->orderBy('created_at', 'DESC');
        // ->with([
        //     'lastAction' => fn($q) => $q->where('action_type', 'requesting_order')
        // ])->whereHas('steps.actions', fn ($q) => $q->where('hd_step_detail.department_code', $department_code))

        return $this->applyScopes($query);
    }

    public function htmlDrawCallbackFunction(): string|null
    {
        return <<<JS

            const _selfTable = $(this);
            $(_selfTable).addClass('table-bordered');
            const loadingElement = '<div class="loading-spinner"></div>';

            // update step
            $(document).off('click', '.update_step_btn');
            $(document).on('click', '.update_step_btn', function(){
                const order_id = $(this).data('order-id');
                const action_code = $(this).data('action')
                const status = $(this).data('status');
                const type = $(this).data('type');
                $('#confirm-update-action-modal').modal('show');

                $(document)
                    .off('click', '#confirm-update-action-btn')
                    .on('click', '#confirm-update-action-btn', function(){
                        const note = $('#form-confirm-action').find('textarea[name="note"]').val();
                        $.ajax({
                        url: "/admin/update-step",
                        method: "POST",
                        data: {order_id,action_code,type,status, note},
                        success: res => {
                            if(res.success) $(_selfTable).DataTable().ajax.reload();
                            $('#confirm-update-action-modal').modal('hide');
                            $('#form-confirm-action').get(0).reset();
                        }
                    })
                })
            });

              //show step detail
              $(document).on('click', '.show_detail_status_btn', function(){
                const step_detail_id = $(this).data('id');
                $('#statusDetailModal .modal-body')
                   .html(loadingElement)
                   .addClass('on-loading position-relative');
                $.ajax({
                    method: 'GET',
                    url: '/admin/admin-handee-retails/step-detail',
                    contentType: 'html',
                    data: {step_detail_id},
                    success: res => {
                        $('#statusDetailModal .modal-body').html(res);
                    }
                })
            });

            // Xóa order
            $(document)
                .off('click', '.cancel-btn')
                .on('click', '.cancel-btn', function(){
                    const order_id = $(this).data('id');
                    $.ajax({
                        method: 'POST',
                        url: '/admin/retail/sale/purchase-order/delete/' + order_id,
                        success: res => {
                            if(!res.error) $(_selfTable).DataTable().ajax.reload();
                        }
                    })
                })
        JS;
    }

    public function columns(): array
    {
        return [
            IdColumn::make('id'),
            Column::make('code')
                ->addClass('text-primary')
                ->title('Mã đơn hàng')
                ->searchable(false)
                ->orderable(false),
            Column::make('order_code')
                ->addClass('text-primary')
                ->title('Số YCSX')
                ->searchable(false)
                ->orderable(false),
            Column::make('customer_name')
                ->title('Tên khách hàng')
                ->searchable(false)
                ->orderable(false),
            Column::make('amount')
                ->title('Giá sản xuất')
                ->nowrap()
                ->orderable(false),
            Column::make('expected_date')
                ->nowrap()
                ->title('Ngày cần hàng')
                ->orderable(false)
                ->searchable(false),
            Column::make('sended_by')
                ->title('Người gửi')
                ->searchable(false)
                ->orderable(false),
            Column::make('confirm_by')
                ->title('Người duyệt')
                ->searchable(false)
                ->orderable(false),
            Column::make('status')
                ->nowrap()
                ->title('Trạng thái')
                ->orderable(false)
                ->searchable(false),
            Column::make('operators')
                ->title('Tùy chọn')
                ->searchable(false)
                ->orderable(false)
        ];
    }

    public function buttons(): array
    {
        return [];
        return $this->addCreateButton(route('retail.sale.quotation.create'), 'purchase-order.create');
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function getBulkChanges(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }
}
