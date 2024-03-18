<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Enums\ProposalProductEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProposalHubIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ProposalHubIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-hub-issue.edit'),
                DeleteAction::make()
                    ->route('proposal-hub-issue.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())

            ->editColumn('proposal_code', function (ProposalHubIssue $item) {
                return BaseHelper::clean(get_proposal_issue_product_code($item->proposal_code));
            })
            ->editColumn('general_order_code', function (ProposalHubIssue $item) {

                return $item->general_order_code ?: '—';


            })->editColumn('operator', function (ProposalHubIssue $item) {
                return view('plugins/hub-warehouse::actions.hub-proposal-issue', compact('item'))->render();
            })->editColumn('warehouse_type', function (ProposalHubIssue $item) {
                return $item->warehouse
                    ? ($item->warehouse?->hub
                        ? $item->warehouse->name . ' - ' . $item->warehouse->hub->name
                        : ($item->warehouse?->agent
                            ? $item->warehouse->name . ' - ' . $item->warehouse->agent->name
                            : ($item->warehouse?->showroom
                                ? $item->warehouse->name . ' - ' . $item->warehouse->showroom->name
                                : 'Thành phẩm: ' . $item->warehouse->name
                            )
                        )
                    )
                    : 'Xuất đi giải';
            })
            ->editColumn('warehouse_name', function (ProposalHubIssue $item) {

                return $item->warehouseIssue->name . ' - ' . $item->warehouseIssue->hub->name  ;
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $current_list_hub_id = get_list_hub_id_for_current_user();//Get id showroom of current user

        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'general_order_code',
                'proposal_code',
                'warehouse_issue_id',
                'warehouse_name',
                'warehouse_address',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_id',
                'warehouse_type',
                'is_warehouse',
                'quantity',
                'title',
                'description',
                'expected_date',
                'reason_cancel',
                'date_confirm',
                'proposal_receipt_id',
                'status',
            ])->orderByRaw("
            CASE status
                WHEN 'pending' THEN 1
                WHEN 'examine' THEN 2
                WHEN 'approved' THEN 3
                WHEN 'denied' THEN 4
                WHEN 'refuse' THEN 5
                WHEN 'confirm' THEN 6
                ELSE 7
            END ASC
        ")->orderBy('expected_date', 'asc');
        ;
        if(\Auth::user()->isSuperUser()){
            return $this->applyScopes($query);
        }
        return $this->applyScopes($query->whereIn('warehouse_issue_id', $current_list_hub_id));
    }

    public function columns(): array
    {
        return [
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->orderable(false)->width(50),
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->orderable(false),
            Column::make('warehouse_name')
                ->title('Kho xuất'),
            Column::make('warehouse_type')
                ->title('Kho nhận'),
            Column::make('title')
                ->title('Mục đích')
                ->orderable(false),
            Column::make('quantity')
                ->title('Số lượng')
                ->orderable(false),
            Column::make('invoice_issuer_name')->title('Người đề xuất')
                ->orderable(false),
            CreatedAtColumn::make('expected_date')
                ->dateFormat('d/m/y')
                ->title('Ngày dự kiến')
                ->orderable(false),
            CreatedAtColumn::make('date_confirm')
                ->dateFormat('d/m/y')
                ->title('Ngày duyệt')
                ->orderable(false),
            StatusColumn::make('status')
                ->title('Trạng thái')
                ->width(50)
                ->orderable(false),
            Column::make('operator')
                ->width(100)->title('Hành động')
                ->orderable(false)
                ->searchable(false),

        ];
    }

    public function buttons(): array
    {
        if ($this->hasPermission('proposal-hub-issue.create') || $this->hasPermission('hub-warehouse.all-permissions')) {
            return $this->addCreateButton(route('proposal-hub-issue.create'));
        }
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
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }


    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
