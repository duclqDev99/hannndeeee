<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Enums\ApprovedStatusEnum;
use Botble\WarehouseFinishedProducts\Models\WarehouseUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReceiptProductTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ReceiptProduct::class)
            ->addActions([
                EditAction::make()
                    ->route('receipt-product.edit'),
                DeleteAction::make()
                    ->route('receipt-product.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('proposal_code', function (ReceiptProduct $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->receipt_code));
            })
            ->editColumn('invoice_confirm_name', function (ReceiptProduct $item) {
                if (empty($item->invoice_confirm_name)) {
                    return '--------';
                }
                return $item->invoice_confirm_name;
            })
            ->editColumn('date_confirm', function (ReceiptProduct $item) {
                if (empty($item->date_confirm)) {
                    return '--------';
                }
                return $item->date_confirm;
            })
            ->editColumn('general_order_code', function (ReceiptProduct $item) {
                if (!empty($item->general_order_code)) {
                    return $item->general_order_code;
                }
                return '--------';
            })
            ->editColumn('operators', function (ReceiptProduct $item) {
                $actionPurchase = '';
                if ($item->status == ApprovedStatusEnum::PENDING) {
                    if ($this->hasPermission('receipt-product.censorship')) {
                        $actionPurchase = '
                        <a data-bs-toggle="tooltip" data-bs-original-title="Xác nhận đơn" href="' . route('receipt-product.censorship', $item->id) . '" class="btn btn-sm btn-icon btn-success"><i class="fa-solid fa-file-import"></i><span class="sr-only">Xác nhận đơn</span></a>
                        ';
                    }
                }

                $actionView = '';
                if ($item->status != ApprovedStatusEnum::PENDING || !Auth::user()->hasPermission('receipt-product.index')) {
                    $actionView = '
                    <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' . route('receipt-product.view', $item) . '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                    ';
                }

                return '
                <div class="table-actions d-flex" style="gap: 5px;">
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
                'general_order_code',
                'proposal_id',
                'warehouse_id',
                'warehouse_name',
                'warehouse_address',
                'isser_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'wh_departure_id',
                'wh_departure_name',
                'is_warehouse',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'status',
                'from_product_issue',
                'created_at',
                'receipt_code',
            ])->orderByRaw("
            CASE status
                WHEN 'pending' THEN 1
                WHEN 'wait' THEN 2
                WHEN 'confirm' THEN 3
                WHEN 'denied' THEN 4
                WHEN 'refuse' THEN 5
                WHEN 'approved' THEN 6
                ELSE 7
            END ASC
        ")->orderByDesc('created_at');
        if (!$this->hasPermission('warehouse-finished-products.warehouse-all')) {
            $warehouseUsers = WarehouseUser::where('user_id', \Auth::id())->get();
            $warehouseIds = $warehouseUsers->pluck('warehouse_id')->toArray();
            $query->whereIn('warehouse_id', $warehouseIds);
        }
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->width(120)
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->width(150)
                ->orderable(false),
            Column::make('title')
                ->title('Tiêu đề')
                ->width(350)
                ->orderable(false)
                ->searchable(false),
            Column::make('warehouse_name')
                ->title('Tên kho nhập')
                ->width(250)
                ->orderable(false)
                ->searchable(false),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến')
                ->width(100)
                ->orderable(false)
                ->searchable(false),
            CreatedAtColumn::make('date_confirm')
            ->dateFormat('d/m/Y')
            ->title('Ngày nhập kho')
            ->width(100)
            ->orderable(false)
            ->searchable(false),
            CreatedAtColumn::make()
                ->dateFormat('d/m/Y')
                ->title('Ngày tạo')
                ->width(100),
            StatusColumn::make()
                ->title('Trạng thái'),
            Column::make('operators')
                ->title('Chức năng')
                ->width(100)
                ->orderable(false)
                ->searchable(false)
        ];
    }

    public function buttons(): array
    {
        return [];
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
            'warehouse_name' => [
                'title' => 'Kho thành phẩm',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'title' => [
                'title' => 'Tiêu đề',
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'expected_date' => [
                'title' => 'Ngày dự kiến',
                'type' => 'date',
            ],
            'date_confirm' => [
                'title' => 'Ngày duyệt',
                'type' => 'date',
            ],
            'created_at' => [
                'title' => 'Ngày tạo',
                'type' => 'date',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => ApprovedStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', ApprovedStatusEnum::values()),
            ],
        ];
    }
}
