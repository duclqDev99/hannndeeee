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

class OrderProductionTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Order::class)
            ->addActions([
                // PrevStepAction::make()
                //     ->route('order-production.prepare-id-for-action'),
                // NextStepAction::make()
                //     ->route('order-production.prepare-id-for-action'),
                EditAction::make()
                    ->route('order-production.edit'),
                DeleteAction::make()
                ->route('order-production.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('username', function (Order $item) {
                if (!$this->hasPermission('order-production.edit')) {
                    return BaseHelper::clean($item->username);
                }
                return Html::link(route('order-production.edit', $item->getKey()), BaseHelper::clean($item->username));
            })
            ->editColumn('level', function (Order $item) {
                return BaseHelper::clean(trans('plugins/sales::sales.customer.level.' .  $item->level));
            })
            ->editColumn('type_order', function (Order $item) {
                return BaseHelper::clean(trans('plugins/sales::orders.' .  $item->type_order));
            })
            ->editColumn('operators', function(Order $item){
                $actionEdit = '
                <a data-bs-toggle="tooltip" data-bs-original-title="Edit" href="' . route('order-production.edit', $item) . '" class="btn btn-sm btn-icon btn-primary">
                    <span class="icon-tabler-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                            <path d="M16 5l3 3"></path>
                        </svg>
                    </span>
                    <span class="sr-only">Edit</span>
                </a>
                ';
                $actionDelete = '';

                if ($item->status == OrderStatusEnum::PENDING || $item->status == OrderStatusEnum::CANCELED) {
                    $actionDelete = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Delete" href="'. route('order-production.destroy', $item) .'" class="btn btn-sm btn-icon btn-danger" data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true" data-confirmation-modal-title="Confirm delete" data-confirmation-modal-message="Do you really want to delete this record?" data-confirmation-modal-button="Delete" data-confirmation-modal-cancel-button="Cancel">
                        <span class="icon-tabler-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 7l16 0"></path>
                                <path d="M10 11l0 6"></path>
                                <path d="M14 11l0 6"></path>
                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                            </svg>
                        </span>
                        <span class="sr-only">Delete</span>
                    </a>
                    ';
                }
                return '
                <div class="table-actions">
                    ' .$actionEdit. '
                    ' .$actionDelete. '
                </div>
                ';
            });
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        // $department_code = get_department_code_curr_user();
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
                'description',
                'expected_date',
                'date_confirm',
                'created_at',
                'status',
            ]);

        return $this->applyScopes($query);
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
            Column::make('title')
                ->title('Tiêu đề')
                ->width(250)
                ->orderable(false),
            Column::make('username')
                ->title('Tên khách hàng')
                ->orderable(false),
            Column::make('invoice_issuer_name')
                ->title('Người tạo đơn')
                ->orderable(false),
            CreatedAtColumn::make('expected_date')
            ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến')
                ->orderable(false),
            CreatedAtColumn::make('date_confirm')
            ->dateFormat('d/m/Y')
                ->title('Ngày hoàn thành')
                ->orderable(false),
            CreatedAtColumn::make()
            ->dateFormat('d/m/Y'),
            StatusColumn::make('status')
                ->title('Trạng thái đơn'),
            Column::make('operators')
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('order-production.create'), 'order-production.create');
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
