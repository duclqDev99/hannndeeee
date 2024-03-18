<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Actions\DetailAction;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\CreatedAtColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Blade;

class MaterialBatchTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(MaterialBatch::class)
            ->addActions([
                DetailAction::make()
                    ->route('proposal-goods-issue.edit'),
            ])
            ->removeAllActions();
        ;

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('stock_name', function (MaterialBatch $item) {
                return BaseHelper::clean($item->stock->name);
            })
            ->editColumn('receipt_name', function (MaterialBatch $item) {
                return BaseHelper::clean($item->receipt->invoice_confirm_name);
            })
            ->editColumn('type_purchase', function (MaterialBatch $item) {
                if ($item->is_order_goods === 0) {
                    return BaseHelper::clean('Nhập từ kho');
                }
                return BaseHelper::clean('Mua hàng');
            })
            ->editColumn('material_name', function (MaterialBatch $item) {
                return $item->material->name;
            })
            ->editColumn('material_code', function (MaterialBatch $item) {
                return $item->material->code;
            })
        ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $materialStockId = get_material_stock_id_by_request();

        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'stock_id',
                'receipt_id',
                'batch_code',
                'material_id',
                'quantity',
                'is_order_goods',
                'status',
                'created_at',
            ])->orderByDesc('created_at')->orderBy('quantity')->where(['stock_id' => $materialStockId]);


        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('receipt_name')
                ->title('Người nhập kho')
                ->width(200)->searchable(false)
                ->orderable(false),
            Column::make('type_purchase')
                ->title('Loại nhập lô')
                ->width(200)->searchable(false)
                ->orderable(false),
            Column::make('batch_code')
                ->title('Mã lô hàng')
                ->orderable(false),
            Column::make('material_name')->searchable(false)
                ->title('Nguyên phụ liệu')
                ->orderable(false),
            Column::make('material_code')
                ->title('Mã nguyên liệu')
                ->orderable(false),
            Column::make('quantity')->searchable(false)
                ->title('Số lượng')
                ->orderable(false),
            CreatedAtColumn::make()->dateFormat('d-m-y')->title('Ngày nhập kho')
        ];
    }

    public function buttons(): array
    {
        return [
            'product-qrcode-scan' => [
                'text' => Blade::render('<span ><x-core::icon name="fa-solid fa-qrcode"/> {{ $title }} </span>', [
                    'title' => 'Quét QR',
                ]),
                'class' => 'btn-primary open_scan_pc_modal',
            ]
        ];


    }

    public function getCheckboxColumnHeading(): array
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
        return [
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
            'material_code' => [
                'title' => 'Mã nguyên phụ liệu',
                'type' => 'text',
            ],

        ];
    }
}
