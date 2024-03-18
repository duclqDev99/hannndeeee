<?php

namespace Botble\OrderAnalysis\Tables;

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

class CustomOrderTable extends OrderTable
{
    protected $department_code = "r_01";

    public function setup(): void
    {
        // parent::setup();

        $this
            ->model(Order::class)
            ->addActions([
                EditAction::make()
                    ->route('order-analyses.edit'),
            ]);
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
            // ->editColumn('day_status', function (Order $item) {
            //     $orderRelationship = $item->orderDepartments->where('department_code', $this->department_code)->first();
            //     $expected_date = Carbon::parse($orderRelationship->expected_date);
            //     $completion_date = Carbon::parse($orderRelationship->completion_date);
            //     $today = Carbon::now();
            //     if($orderRelationship == 'completed'){
            //         $isBefore = $expected_date->isBefore($completion_date);
            //     }elseif($orderRelationship != 'waiting'){
            //         $isBefore = $expected_date->isBefore($today);
            //     }
            //     // $date1 = Carbon::parse('2024-01-02');
            //     // $date2 = Carbon::parse('2024-01-03');
            //     // $isBefore = $date1->isBefore($date2);
            //     switch ($isBefore) {
            //         case true:
            //             return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', 'Hoàn thành đúng giờ', 'success'));
            //             break;
            //         case false:
            //             return Blade::render(sprintf('<x-core::badge label="%s" color="%s" />', 'Hoàn thành muộn', 'danger'));
            //             break;
            //         default:
            //             return '';
            //             break;
            //     };


            //     return $orderRelationship->completion_date;
            // })
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
            // StatusColumn::make('status')
            //     ->title('Trạng thái đơn'),
            // Column::make('procedure_code')
            //     ->title('Quy trình hiện tại')
            //     ->width(100)
            //     ->orderable(false)
            //     ->searchable(false),
        ];
    }

    public function buttons(): array
    {
        return [];
    }
}
