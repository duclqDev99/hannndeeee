<?php

namespace Botble\Showroom\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ShowroomIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ShowroomIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('showroom-issue.edit'),
                DeleteAction::make()
                    ->route('showroom-issue.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ShowroomIssue $item) {
                if (!$this->hasPermission('agent-issue.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('agent-issue.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('issue_code', function (ShowroomIssue $item) {
                return BaseHelper::clean(get_proposal_issue_product_code($item->issue_code));
            })
            ->editColumn('warehouse', function (ShowroomIssue $item) {
                return $item->warehouse?->name . ' - ' . $item->warehouse?->hub->name;
            })
            ->editColumn('warehouse_name', function (ShowroomIssue $item) {
                return $item->warehouseIssue?->name . ' - ' . $item->warehouseIssue?->showroom->name;
            })
            ->editColumn('general_order_code', function (ShowroomIssue $item) {
                return $item->general_order_code ?: '—';
            })
            ->editColumn('operator', function (ShowroomIssue $item) {

                return view('plugins/showroom::actions.showroom-issue', compact('item'))->render();
            });

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
                'warehouse_issue_id',
                'proposal_id',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_id',
                'warehouse_type',
                'general_order_code',
                'title',
                'description',
                'expected_date',
                'date_confirm',
                'status',
                'reason',
                'issue_code',
            ])->orderByRaw(" CASE status

            WHEN 'pending' THEN 1
            WHEN 'denied' THEN 2
            WHEN 'approved' THEN 3
            ELSE 4
             END ASC
            ");

        if(\Auth::user()->isSuperUser()){
            return $this->applyScopes($query);
        }
        return $this->applyScopes($query->whereIn('warehouse_issue_id', $current_list_shoowroom_id));
    }

    public function columns(): array
    {
        return [
            Column::make('issue_code')->title('Mã phiếu'),
            Column::make('general_order_code')->title('Mã đơn hàng'),
            Column::make('warehouse_name')->title('Kho xuât'),
            Column::make('warehouse')->title('Kho nhập')->orderable(false)->searchable(false),
            Column::make('title')->title('Tiêu đề'),
            CreatedAtColumn::make('expected_date')->dateFormat('d/m/y')->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')->dateFormat('d/m/y')->title('Ngày xuất kho'),
            StatusColumn::make(),
            Column::make('operator')
                ->title('Hành động')
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
