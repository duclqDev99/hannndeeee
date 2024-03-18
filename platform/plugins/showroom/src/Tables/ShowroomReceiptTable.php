<?php

namespace Botble\Showroom\Tables;

use Botble\Agent\Actions\ConfirmAction;
use Botble\Base\Facades\BaseHelper;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ShowroomReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ShowRoomReceipt::class)
            ->addActions([
                ConfirmAction::make()
                    ->route('showroom-receipt.confirmView'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('warehouse_receipt_id', function (ShowRoomReceipt $item) {
                return $item->warehouseReceipt->name . ' - ' . $item->warehouseReceipt->showroom->name;
            })
            ->editColumn('warehouse', function (ShowRoomReceipt $item) {
                return $item->warehouse->name . ' - ' . $item->warehouse->hub->name;
            })
            ->editColumn('receipt_code', function (ShowRoomReceipt $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->receipt_code));
            })
            ->editColumn('general_order_code', function (ShowRoomReceipt $item) {
                return $item->general_order_code ?: '—';
            })
            ->editColumn('quantity', function (ShowRoomReceipt $item) {
                return $item->receiptDetail->sum('quantity');
            })
            ->editColumn('operators', function (ShowRoomReceipt $item) {
                return view('plugins/showroom::actions.showroom-receipt', compact('item'))->render();
            });
        ;
        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $current_list_shoowroom_id = get_list_showroom_id_for_current_user();//Get id showroom of current user

        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'warehouse_receipt_id',
                'proposal_id',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_id',
                'warehouse_type',
                'general_order_code',
                'quantity',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'reason_cancel',
                'receipt_code',
                'status',
            ])->orderByRaw(" CASE status

            WHEN 'pending' THEN 1
            WHEN 'cancel' THEN 2
            WHEN 'approved' THEN 3
            ELSE 4
             END ASC
            ");
        if(\Auth::user()->isSuperUser()){
            return $this->applyScopes($query);
        }
        return $this->applyScopes($query->whereIn('warehouse_receipt_id', $current_list_shoowroom_id));
    }

    public function columns(): array
    {
        return [

            Column::make('receipt_code')->title('Mã phiếu nhập'),
            Column::make('general_order_code')->title('Mã đơn hàng'),
            Column::make('warehouse_receipt_id')->title('Kho nhập'),
            Column::make('warehouse')->title('Kho xuất'),
            Column::make('title')->title('Mục đích nhập kho'),
            Column::make('quantity')->title('Số lượng'),
            CreatedAtColumn::make('expected_date')->title('Ngày dự kiến')->dateFormat('d-m-Y'),
            CreatedAtColumn::make('date_confirm')->title('Ngày nhập'),
            StatusColumn::make(),
            Column::make('operators')
                ->title('Hành động')
                ->width(100)
                ->orderable(false)
                ->searchable(false)
        ];
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
        return $this->getBulkChanges();
    }

    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
}
