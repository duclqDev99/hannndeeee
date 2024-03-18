<?php

namespace Botble\OrderRetail\Tables\Accountant;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Media\Facades\RvMedia;
use Botble\Sales\Models\Customer;
use Botble\OrderRetail\Models\Order;
use Botble\OrderRetail\Models\OrderQuotation;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class QuotationTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(OrderQuotation::class)
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

            ->editColumn('customer_name', function (OrderQuotation $item) {
                return $item->order->customer_name;
            })
            ->editColumn('contract', function (OrderQuotation $item) {
                return '';
                $url = RvMedia::getImageUrl($item->contract->url);
                return '<a href="'.$url.'" download>'.$item->contract->extras['filename'].'</a>';
            })

            ->editColumn('status', function (OrderQuotation $item) {
                return 'Processing';
            })
            ->editColumn('payment_status', function (OrderQuotation $item) {
                $customerDeposit = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::CUSTOMER_DEPOSIT, $item->order->id);
                $text = $customerDeposit->status == 'confirmed' ? 'Đã cọc tiền' : 'Chưa cọc tiền';
                return $text;
            })
            ->editColumn('amount', function (OrderQuotation $item) {
                return  number_format($item->amount, 0, ',', '.') . '₫';
            })
            ->editColumn('created_by', function (OrderQuotation $item) {
                $created = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_CREATE_QUOTATION, $item->order->id);
                if ($created) {
                    $created->load('handler:id,first_name,last_name');
                }
                return $created?->handler?->name;
            })
            // ->editColumn('status', function (OrderQuotation $item) {
            //     return $item->order->lastAction->actionSetting->title;
            // })
            ->editColumn('operators', function (OrderQuotation $item) {
                return view('plugins/order-retail::accountant.quotation.dropdown-actions', compact('item'));
            });
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        // $department_code = get_department_code_curr_user()[0]->department_code;
        $department_code = 'retail_accountant';
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'title',
                'amount',
                'start_date',
                'due_date',
                'shipping_amount',
                'note',
                'order_code',
                'status',
            ])
            ->with([
                'order:id,code,customer_name,amount',
                'order.lastACtion' => fn ($q) => $q->whereRelation('actionSetting', 'hd_action_setting.action_type', 'production_order')
            ])
            ->whereHas('order.steps', function ($q) use ($department_code) {
                $q->where('is_ready', true);
                $q->whereHas('actions', function ($q) use ($department_code) {
                    $q->where('status', '!=', ActionStatusEnum::NOT_READY);
                    $q->whereRelation('actionSetting', 'hd_action_setting.department_code', $department_code);
                    $q->whereRelation('actionSetting', 'hd_action_setting.action_type', 'quotation_order');
                });
            })
            ->orderBy('created_at', 'DESC')
            ->when(request()->has('order_code'), fn($q) => $q->whereRelation('order', 'code', '#'. request('order_code')));

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

            Column::make('order_code')
                ->addClass('text-primary')
                ->title('Số YCSX')
                ->searchable(false)
                ->orderable(false),
            Column::make('title')
                ->title('Tiêu đề')
                ->orderable(false),
            Column::make('customer_name')
                ->title('Tên khách hàng')
                ->searchable(false)
                ->orderable(false),
            Column::make('contract')
                ->alignCenter()
                ->nowrap()
                ->title('Hợp đồng')
                ->orderable(false)
                ->searchable(false),
            Column::make('payment_status')
                ->title('Tình trạng cọc tiền')
                ->searchable(false)
                ->orderable(false),
            Column::make('amount')
                ->title('Tổng tiền')
                ->nowrap()
                ->orderable(false),
            Column::make('created_by')
                ->title('Người tạo')
                ->searchable(false)
                ->orderable(false),
            // Column::make('status')
            //     ->nowrap()
            //     ->title('Trạng thái')
            //     ->orderable(false)
            //     ->searchable(false),
            Column::make('operators')
                ->title('Tùy chọn')
                ->searchable(false)
                ->orderable(false)
        ];
    }

    public function buttons(): array
    {
        return [];
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
        return [];
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
