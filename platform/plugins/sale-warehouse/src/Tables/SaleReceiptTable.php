<?php

namespace Botble\SaleWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\SaleWarehouse\Models\SaleReceipt;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
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

class SaleReceiptTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(SaleReceipt::class)
            ->addActions([
                EditAction::make(),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('warehouse_receipt_id', function (SaleReceipt $item) {
                return $item->warehouseReceipt->name . ' - ' . $item->warehouseReceipt->saleWarehouse->name;
            })
            ->editColumn('warehouse', function (SaleReceipt $item) {
                return $item->warehouse->name . ' - ' . $item->warehouse->hub->name;
            })
            ->editColumn('receipt_code', function (SaleReceipt $item) {
                return BaseHelper::clean(get_proposal_receipt_product_code($item->receipt_code));
            })
            ->editColumn('general_order_code', function (SaleReceipt $item) {
                return $item->general_order_code ?: '—';
            })
            ->editColumn('quantity', function (SaleReceipt $item) {
                return $item->receiptDetail->sum('quantity');
            })
            ->editColumn('operators', function (SaleReceipt $item) {
                return view('plugins/sale-warehouse::actions.sale-receipt', compact('item'))->render();
            })
           ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $current_list_sale_id = get_list_sale_warehouse_id_for_current_user();

        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'warehouse_receipt_id',
                'hub_issue_id',
                'receipt_code',
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
                'status',
            ])->orderByRaw(" CASE status

            WHEN 'pending' THEN 1
            WHEN 'cancel' THEN 2
            WHEN 'approved' THEN 3
            ELSE 4
             END ASC
            ");
            if(\Auth::user()->hasPermission('sale-warehouse.all')){
                return $this->applyScopes($query);
            }

            return $this->applyScopes($query->whereIn('warehouse_receipt_id', $current_list_sale_id));

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

    // public function buttons(): array
    // {
    //     return $this->addCreateButton(route('sale-receipt.create'), 'sale-receipt.create');
    // }

    public function bulkActions(): array
    {
        return [
            // DeleteBulkAction::make()->permission('sale-receipt.destroy'),
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
}
