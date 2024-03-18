<?php

namespace Botble\OrderAnalysis\Tables;

use Botble\OrderAnalysis\Models\OrderAnalysis;
use Botble\Sales\Models\Order;
use Botble\Sales\Actions\DeleteAction;
use Botble\Sales\Enums\OrderStatusEnum;
use Botble\Sales\Tables\OrderTable;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class OrderQuotationTable extends OrderTable
{
    public function setup(): void
    {
        // parent::setup();

        $this
            ->model(Order::class)
            ->addActions([
                EditAction::make()
                    ->route('order-quotation.index'),
                DeleteAction::make()
                ->route('order-quotation.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('operators', function(Order $item){
                $actionEdit = '
                <a data-bs-toggle="tooltip" data-bs-original-title="Edit" href="' . route('order-quotation.approve', $item) . '" class="btn btn-sm btn-icon btn-primary">
                    <span class="icon-tabler-wrapper">
                    <i class="fa-solid fa-paper-plane"></i>
                    </span>
                    <span class="sr-only">Edit</span>
                </a>
                ';
                $actionDelete = '';

                if ($item->status == OrderStatusEnum::PENDING || $item->status == OrderStatusEnum::CANCELED) {
                    $actionDelete = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Delete" href="'. route('purchase-order.destroy', $item) .'" class="btn btn-sm btn-icon btn-danger" data-dt-single-action="" data-method="DELETE" data-confirmation-modal="true" data-confirmation-modal-title="Confirm delete" data-confirmation-modal-message="Do you really want to delete this record?" data-confirmation-modal-button="Delete" data-confirmation-modal-cancel-button="Cancel">
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
        $department_code = get_department_code_curr_user();
        $nameModel = OrderAnalysis::class;
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
            ])->whereHas('attachs', function ($query) use ($nameModel) {
                $query->where('attach_type', $nameModel);
            });

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('order_code')
                ->title('Mã đơn hàng')
                ->orderable(false),
            Column::make('title')
                ->title('Tiêu đề')
                ->width(250)
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
            ->title('Chức năng')
            ->width(50)
        ];
    }

    public function buttons(): array
    {
        return [];
    }
}
