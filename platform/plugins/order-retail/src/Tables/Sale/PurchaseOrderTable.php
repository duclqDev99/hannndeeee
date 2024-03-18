<?php

namespace Botble\OrderRetail\Tables\Sale;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sales\Models\Customer;
use Botble\OrderRetail\Models\Order;
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

class PurchaseOrderTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(Order::class)
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
            ->editColumn('level', function (Order $item) {
                return BaseHelper::clean(trans('plugins/sales::sales.customer.level.' .  $item->level));
            })
            ->editColumn('status', function (Order $item) {
                return "Processing";
            })
            ->editColumn('amount', function (Order $item) {
                return  number_format($item->amount, 0, ',', '.') . '₫';
            })
            ->editColumn('created_by', function (Order $item) {
                $created = get_action(\Botble\OrderStepSetting\Enums\ActionEnum::RETAIL_SALE_CREATE_ORDER, $item->id);
                if ($created) {
                    $created->load('handler:id,first_name,last_name');
                }
                return $created?->handler?->name;
            })
            ->editColumn('status', function (Order $item) {
                return $item->lastAction->actionSetting->title;
            })
            ->editColumn('operators', function (Order $item) {
                return view('plugins/order-retail::sale.purchase-order.dropdown-actions', compact('item'));
            });
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        // $department_code = get_department_code_curr_user()[0]->department_code;
        $department_code = 'retail_sale';
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'code',
                'order_type',
                'customer_name',
                'customer_phone',
                'amount',
                'note',
                'expected_date',
                'created_at',
                'status',
            ])
            ->with([
                'lastAction'
            ])
            ->when(request('order_code'), fn ($q) => $q->where('code', '#' . request('order_code')))
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
                ->title('Số YCSX')
                ->orderable(false),
            StatusColumn::make('order_type')
                ->title('Loại đơn')
                ->searchable(false)
                ->orderable(false),
            Column::make('customer_name')
                ->title('Tên khách hàng')
                ->searchable(false)
                ->orderable(false),
            Column::make('amount')
                ->nowrap()
                ->title('Trị giá')
                ->orderable(false)
                ->searchable(false),
            Column::make('expected_date')
                ->dateFormat('d/m/Y')
                ->nowrap()
                ->title('Ngày cần hàng')
                ->orderable(false)
                ->searchable(false),
            Column::make('created_by')
                ->title('Sale')
                ->searchable(false)
                ->orderable(false),
            CreatedAtColumn::make('created_at')
                ->dateFormat('d/m/Y')
                ->title('Ngày tạo')
                ->nowrap()
                ->orderable(false),
            Column::make('status')
                ->title('Trạng thái'),
            Column::make('operators')
                ->title('Tùy chọn')
                ->searchable(false)
                ->orderable(false)
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('retail.sale.purchase-order.create'), 'retail.sale.purchase-order.create');
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
