<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\HubWarehouse\Models\HubUser;
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

class HubIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(HubIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('hub-issue.edit'),
                DeleteAction::make()
                    ->route('hub-issue.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (HubIssue $item) {
                if (!$this->hasPermission('hub-issue.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('hub-issue.edit', $item->getKey()), BaseHelper::clean($item->name));
            })->editColumn('warehouse_type', function (HubIssue $item) {
                if ($item?->proposal?->is_warehouse && $item?->proposal?->is_warehouse == 6) {
                    return 'Xuất đi giải';
                }
                if (isset ($item->warehouse->hub) && $item->warehouse->hub->name) {
                    return $item->warehouse->name . ' - ' . $item->warehouse->hub->name;
                } elseif (isset ($item->warehouse->showroom) && $item->warehouse->showroom->name) {
                    return $item->warehouse->name . ' - ' . $item->warehouse->showroom->name;
                } elseif (isset ($item->warehouse->agent) && $item->warehouse->agent->name) {
                    return $item->warehouse->name . ' - ' . $item->warehouse->agent->name;
                } else {
                    return $item->warehouse->name;
                }
            })->editColumn('quantity', function (HubIssue $item) {
                return $item->productIssueDetail->sum('quantity');
            })
            ->editColumn('issue_code', function (HubIssue $item) {
                return BaseHelper::clean(get_proposal_issue_product_code($item->issue_code));
            })
            ->editColumn('warehouse_name', function (HubIssue $item) {
                return $item->warehouseIssue->hub?->name
                    ? $item->warehouse_name . ' - ' . $item->warehouseIssue->hub->name
                    : $item->warehouse_name;
            })->editColumn('operator', function (HubIssue $item) {


                return view('plugins/hub-warehouse::actions.hub-issue',compact('item'))->render();
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        // Assuming you have a 'hub_id' column in HubUser
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'proposal_id',
                'warehouse_issue_id',
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
                'created_at',
                'status',
                'issue_code'
            ])->when(!request()->user()->hasPermission('hub-warehouse.all-permissions'), function ($q) {
                $authUserId = request()->user()->id;
                $userHub = HubUser::where('user_id', $authUserId)->pluck('hub_id');
                if (request()->user()->hasPermission('hub-issue.confirm')) {
                    $q->whereHas('warehouseIssue.hub', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub);
                    });
                } else {
                    $q->whereHas('warehouseIssue.hub', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub)->where('issuer_id', request()->user()->id);
                        ;
                    });
                }
            })->orderByRaw("
            CASE status
                WHEN 'pending_issue' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'denied' THEN 3
                WHEN 'approved' THEN 4
                ELSE 5
            END ASC
        ")->orderBy('expected_date', 'asc');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('issue_code')->title('Mã phiếu'),
            Column::make('warehouse_name')->title('Kho xuât'),
            Column::make('warehouse_type')->title('Nơi đến'),
            Column::make('title')->title('Mục đích'),
            Column::make('quantity')->title('Số lượng')->orderable(false)->searchable(false),
            CreatedAtColumn::make('expected_date')->dateFormat('d/m/y')->title('Ngày dự kiến'),
            CreatedAtColumn::make('date_confirm')->dateFormat('d/m/y')->title('Ngày xuất kho'),
            StatusColumn::make('status')->title('Trạng thái'),
            Column::make('operator')
                ->title('Hành động')
                ->orderable(false)
                ->searchable(false),
        ];
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
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
        ];
    }
}
