<?php

namespace Botble\SaleWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\SaleWarehouse\Models\SaleIssue;
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

class SaleIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(SaleIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('sale-issue.edit'),
                DeleteAction::make()
                    ->route('sale-issue.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('warehouse_type', function (SaleIssue $item) {
                if($item->proposal->is_warehouse == 'tour'){
                    return 'Xuất đi giải';
                }
                return '---';
            })->editColumn('quantity', function (SaleIssue $item) {
                return $item->productIssueDetail->sum('quantity');
            })
            ->editColumn('issue_code', function (SaleIssue $item) {
                return BaseHelper::clean(get_proposal_issue_product_code($item->issue_code));
            })
            ->editColumn('warehouse_name', function (SaleIssue $item) {
                return $item->warehouseIssue->saleWarehouse?->name
                    ? $item->warehouse_name . ' - ' . $item->warehouseIssue->saleWarehouse->name
                    : $item->warehouse_name;
            })->editColumn('operator', function (SaleIssue $item) {


                return view('plugins/sale-warehouse::actions.sale-issue',compact('item'))->render();
            });

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
                'warehouse_issue_id',
                'proposal_id',
                'warehouse_name',
                'warehouse_address',
                'general_order_code',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_id',
                'warehouse_type',
                'issue_code',
                'quantity',
                'expected_date',
                'date_confirm',
                'title',
                'description',
                'status',
            ])->orderByRaw("
            CASE status
                WHEN 'pending_issue' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'denied' THEN 3
                WHEN 'approved' THEN 4
                ELSE 5
            END ASC
        ")->orderBy('expected_date', 'asc');

        if(\Auth::user()->hasPermission('sale-warehouse.all')){
            return $this->applyScopes($query);
        }

        return $this->applyScopes($query->whereIn('warehouse_issue_id', $current_list_sale_id));
    }

    public function columns(): array
    {
        return [
            Column::make('issue_code')->title('Mã phiếu'),
            Column::make('warehouse_name')->title('Kho xuât'),
            Column::make('warehouse_type')->title('Nơi đến'),
            Column::make('title')->title('Mục đích'),
            Column::make('quantity')->title('Số lượng'),
            CreatedAtColumn::make('expected_date')->dateFormat('d/m/y')->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')->dateFormat('d/m/y')->title('Ngày xuất kho'),
            StatusColumn::make('status')->title('Trạng thái'),
            Column::make('operator')
                ->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    // public function buttons(): array
    // {
    //     return $this->addCreateButton(route('sale-issue.create'), 'sale-issue.create');
    // }

    // public function bulkActions(): array
    // {
    //     return [
    //         DeleteBulkAction::make()->permission('sale-issue.destroy'),
    //     ];
    // }

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
