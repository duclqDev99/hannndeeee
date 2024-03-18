<?php

namespace Botble\Sales\Tables;

use Botble\Department\Enums\OrderDepartmentStatusEnum;
use Botble\Sales\Models\Order;
use Botble\Sales\Actions\DeleteAction;
use Botble\Sales\Tables\OrderTable;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;

class OrderQuotationTable extends OrderTable
{
    protected $department_code = "admin_001";

    public function setup(): void
    {
        // parent::setup();

        $this
            ->model(Order::class)
            ->addActions([
                EditAction::make()
                    ->route('order-analyses.edit'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $response  = parent::ajax();

        $data = $this->table
            ->eloquent($this->query())
            // Các cột khác...
            ->editColumn('statusDepartment', function (Order $item) {
                $orderRelationship = $item->orderDepartments->where('department_code', $this->department_code)->first();
                switch ($orderRelationship->status) {
                    case 'waiting':
                        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $orderRelationship->status, 'warning'));
                        break;
                    case 'approved':
                        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $orderRelationship->status, 'primary'));
                        break;
                    case 'completed':
                        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $orderRelationship->status, 'success'));
                        break;
                    case 'reject':
                        return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', $orderRelationship->status, 'danger'));
                        break;
                    default:
                        return '';
                        break;
                };
            })
            ->editColumn('expected_date', function (Order $item) {
                $orderRelationship = $item->orderDepartments->where('department_code', $this->department_code)->first();
                return $orderRelationship->expected_date;
            })
            ->editColumn('completion_date', function (Order $item) {
                $orderRelationship = $item->orderDepartments->where('department_code', $this->department_code)->first();
                return $orderRelationship->completion_date;
            })
            ->editColumn('operators', function (Order $item) {
                $orderRelationship = $item->orderDepartments->where('department_code', $this->department_code)->first();

                //Html các chức năng tác vụ sẽ hiển thị dựa vào tình trạng đơn hiện tại
                $cxtCreateQuotation = '';
                $cxtDetailQuotation = '';
                $cxtUpdateQuotation = '';

                if($orderRelationship->status == OrderDepartmentStatusEnum::WAITING){
                    $cxtCreateQuotation = '<li><a class="dropdown-item" href="'.route('order-quotation.create', $item).'">Tạo bản báo giá</a></li>';
                }else if($orderRelationship->status == OrderDepartmentStatusEnum::COMPLETED){
                    $cxtDetailQuotation = '<li><a class="dropdown-item" href="#">Chi tiết báo giá</a></li>';
                    $cxtUpdateQuotation = '<li><a class="dropdown-item" href="#">Cập nhật báo giá</a></li>';
                }else if($orderRelationship->status == OrderDepartmentStatusEnum::CANCELED){
                    $cxtDetailQuotation = '<li><a class="dropdown-item" href="#">Chi tiết báo giá</a></li>';
                }
                return '
                <div class="dropdown">
                    <button class="btn btn-secondary" type="button" id="dropdownMenu-'.$item->id.'" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu-'.$item->id.'">
                        '. $cxtCreateQuotation .'
                        '. $cxtDetailQuotation .'
                        '. $cxtUpdateQuotation .'
                    </ul>
                </div>
                ';   
            })
            ;
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
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
            ])
            ->whereHas('orderDepartments', function($query) {
                $query->where('department_code', $this->department_code);
            })
            ->with('orderDepartments')
            ->orderByRaw("
                CASE status
                    WHEN 'waiting' THEN 1
                    WHEN 'reject' THEN 2
                    WHEN 'approved' THEN 3
                    WHEN 'processing' THEN 4
                    WHEN 'completed' THEN 5
                    ELSE 6
                END ASC
            ")
            ->orderBy('created_at', 'desc');
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
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
            Column::make('invoice_issuer_name')
                ->title('Người tạo đơn')
                ->orderable(false),
            Column::make('expected_date')
                ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến')
                ->orderable(false),
            Column::make('completion_date')
                ->dateFormat('d/m/Y')
                ->title('Ngày hoàn thành')
                ->orderable(false),
            // Column::make('day_status')
            //     ->title('Trạng thái hoàn thành')
            //     ->orderable(false),
            Column::make('statusDepartment')
                ->title('Trạng thái đơn')
                ->orderable(false),
            Column::make('operators')
            ->width(50)
            ->title('Tác vụ')
            ->orderable(false)
        ];
    }

    public function buttons(): array
    {
        return [];
    }
}
