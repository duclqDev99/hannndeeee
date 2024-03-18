<?php

namespace Botble\Sales\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sales\Models\Customer;
use Botble\Sales\Models\Order;
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

class OrderTable extends TableAbstract
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
            ->setView('plugins/sales::table.index');
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
            ->editColumn('status', function (Order $item) {
                return $item->lastAction->title;
            })
            ->editColumn('operators', function (Order $item) {
                return view('plugins/order-retail::table-columns.dropdown-actions', compact('item'));
            });
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $department_code = get_department_code_curr_user()[0]->department_code;
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'type_order',
                'order_code',
                'id_user',
                'username',
                'email',
                'phone',
                'invoice_issuer_name',
                'document_number',
                'title',
                'sub_total',
                'description',
                'expected_date',
                'date_confirm',
                'created_at',
                'status',
                'created_by_id'
            ]);
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
                $.ajax({
                    url: "/admin/update-step",
                    method: "POST",
                    data: {order_id,action_code,type,status},
                    success: res => {
                        if(res.success) $(_selfTable).DataTable().ajax.reload();
                    }
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
        JS;
    }

    public function columns(): array
    {
        return [
            IdColumn::make('id'),
            Column::make('order_code')
                ->title('Mã đơn hàng')
                ->orderable(false),
            StatusColumn::make('type_order')
                ->title('Loại đơn')
                ->orderable(false),
            Column::make('username')
                ->title('Tên khách hàng')
                ->orderable(false),
            Column::make('sub_total')
                ->nowrap()
                ->title('Trị giá')
                ->orderable(false)
                ->searchable(false),
            Column::make('expected_date')
                ->nowrap()
                ->title('Ngày cần hàng')
                ->orderable(false)
                ->searchable(false),
            Column::make('invoice_issuer_name')
                ->title('Người tạo')
                ->orderable(false),
            CreatedAtColumn::make('created_at')
                ->dateFormat('d/m/Y, H:i')
                ->title('Ngày tạo')
                ->nowrap()
                ->orderable(false),
            Column::make('status')
                ->title('Cập nhật mới nhất'),
            Column::make('operators')
                ->title('Tùy chọn')
                ->searchable(false)
                ->orderable(false)
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('purchase-order.create'), 'purchase-order.create');
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
