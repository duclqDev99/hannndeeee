<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Warehouse\Enums\MaterialProposalStatusEnum;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Columns\Column;
use Botble\Theme\Asset;
use Botble\Warehouse\Enums\PurchaseOrderStatusEnum;
use Botble\Warehouse\Models\ReceiptPurchaseGoods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReceiptPurchaseGoodsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ReceiptPurchaseGoods::class)
            ->addActions([
                EditAction::make()
                    ->route('material-receipt-confirm.edit'),
                DeleteAction::make()
                    ->route('material-receipt-confirm.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('proposal_code', function (ReceiptPurchaseGoods $item) {
                return $item->proposal->code;
            })
            ->editColumn('date_confirm', function (ReceiptPurchaseGoods $item) {
                if (!empty($item->date_confirm)) {
                    return $item->date_confirm;
                }
                return '------------';
            })
            ->editColumn('general_order_code', function (ReceiptPurchaseGoods $item) {
                if (!empty($item->general_order_code)) {
                    return $item->general_order_code;
                }
                return '--------';
            })
            ->editColumn('status_update', function (ReceiptPurchaseGoods $item) {
                $role = trans('plugins/warehouse::enums.statuses.purchase-order.' . $item->status);

                if($item->status == PurchaseOrderStatusEnum::APPOROVED){
                    return '';
                }
                return view('plugins/warehouse::partials.po-status', compact('item','role'))->render();
            })
            ->editColumn('operator', function (ReceiptPurchaseGoods $item) {
                $actionView = '
                <a data-bs-toggle="tooltip" data-bs-original-title="View" href="' . route('receipt-purchase-goods.view', $item->id) . '" class="btn btn-sm btn-icon btn-secondary"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                ';

                return '
                <div class="table-actions">
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
                    'general_order_code',
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
                ->title('Kho nhập')
                ->width(200)
                ->searchable(true),
            Column::make('title')
                ->title('Tên phiếu')
                ->orderable(false),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->width(100)
                ->orderable(false),
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->width(100)
                ->orderable(false),
            CreatedAtColumn::make('expected_date')
            ->dateFormat('d/m/Y')
                ->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')
            ->dateFormat('d/m/Y')
                ->title('Ngày nhập'),
            CreatedAtColumn::make('created_at')
            ->dateFormat('d/m/Y')
                ->width(100)
                ->title('Ngày tạo'),
            StatusColumn::make('status')
                ->title('Trạng thái')
                ->width(100)
                ->orderable(false),
            Column::make('status_update')
            ->title('Cập nhật trạng thái')
            ->width(100)
            ->orderable(false)
            ->searchable(false),
            Column::make('operator')
                ->width(50)
                ->searchable(false)
                ->orderable(false),
        ];
    }

    public function htmlDrawCallbackFunction(): string|null
    {
        return parent::htmlDrawCallbackFunction() . '$(".editable").editable({mode: "inline"});';
    }

    public function getCheckboxColumnHeading(): array {
        return [];
    }

    public function buttons(): array
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
