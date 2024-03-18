<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Models\MaterialOutConfirm;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;


use Botble\Table\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MaterialOutReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(MaterialOutConfirm::class)
            ->addActions([
                EditAction::make()
                    ->route('material-plan-receipt.edit'),
                DeleteAction::make()
                    ->route('material-plan-receipt.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('proposal_code', function (MaterialOutConfirm $item) {
                return $item->proposal->proposal_code;
            })
            ->editColumn('invoice_confirm_name', function (MaterialOutConfirm $item) {
                if (!empty($item->invoice_confirm_name)) {
                    return $item->invoice_confirm_name;
                }
                return '------------';
            })
            ->editColumn('status', function (MaterialOutConfirm $item) {
                return $item->status->toHtml();
            })
            ->editColumn('warehouse_type', function (MaterialOutConfirm $item) {
                if ($item->proposal->warehouse_type) {
                    return $item->proposal->warehouse?->name;
                } else {
                    return '-----';
                }
            })


            ->editColumn('total_amount', function (MaterialOutConfirm $item) {
                return format_price($item->total_amount);
            })

            ->editColumn('operator', function (MaterialOutConfirm $item) {
                $actionPurchase = '';
                if ($item->status == MaterialProposalStatusEnum::PENDING && $this->hasPermission('goods-issue-receipt.confirm')) {
                    $actionPurchase = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="Receipt" href="' . route('goods-issue-receipt.issue', $item->id) . '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Receipt</span></a>
                    ';
                }
                $actionView = '';
                if ($actionPurchase == '') {
                    $actionView = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' . route('goods-issue-receipt.view', $item->id) . '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
                }



                return '
                <div class="table-actions">
                    ' . $actionPurchase . '
                    ' . $actionView . '
                 </div>
                ';
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                    'id',
                    'title',
                    'invoice_issuer_name',
                    'invoice_confirm_name',
                    'proposal_id',
                    'warehouse_name',
                    'quantity',
                    'total_amount',
                    'expected_date',
                    'date_confirm',
                    'status',
                    'created_at',
                ])
            ->orderByDesc(DB::raw('POSITION("pending" IN status)'))->orderByDesc('created_at');
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('warehouse_name')
                ->title('Kho xuất')
                ->width(200),
            Column::make('warehouse_type')
                ->title('Xuất đến')
                ->width(200)
                ->orderable(false),
            Column::make('title')
                ->title('Tên phiếu')
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->width(100)
                ->orderable(false),
            // Column::make('invoice_issuer_name')->title('Người duyệt')
            //     ->width(150)
            //     ->orderable(false),
            // Column::make('invoice_confirm_name')->title('Người xuất kho')
            //     ->width(150)
            //     ->orderable(false),
            // Column::make('quantity')
            //     ->title('Tổng số lượng')
            //     ->orderable(false)
            //     ->searchable(false),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/y')
                ->title('Ngày dự kiến')
                ->orderable(false),
            CreatedAtColumn::make('date_confirm')
                ->dateFormat('d/m/y')
                ->title('Ngày duyệt')
                ->orderable(false),
            Column::make('status')
                ->title('Trạng thái')
                ->width(50)
                ->orderable(false)
                ->searchable(false),
            Column::make('operator')
                ->width(50)->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    // public function buttons(): array
    // {
    //     return $this->addCreateButton(route('material-receipt-confirm.create'), 'material-receipt-confirm.create');
    // }
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
    public function bulkActions(): array
    {
        return [
        ];
    }

    public function getBulkChanges(): array
    {
        return [

        ];
    }

    public function getFilters(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }
}
