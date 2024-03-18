<?php

namespace Botble\Showroom\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Showroom\Models\ShowroomProposalIssue;
use Botble\Showroom\Models\ShowroomUser;
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

class ShowroomProposalIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ShowroomProposalIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('showroom-proposal-issue.edit'),
                DeleteAction::make()
                    ->route('showroom-proposal-issue.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (ShowroomProposalIssue $item) {
                if (!$this->hasPermission('showroom-proposal-issue.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('showroom-proposal-issue.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('proposal_code', function (ShowroomProposalIssue $item) {
                return BaseHelper::clean(get_proposal_issue_product_code($item->proposal_code));
            })
            ->editColumn('general_order_code', function (ShowroomProposalIssue $item) {
                return $item->general_order_code ?: '—';
            })
            ->editColumn('operator', function (ShowroomProposalIssue $item) {
                return view('plugins/showroom::actions.showroom-proposal-issue', compact('item'))->render();
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
                'warehouse_issue_id',
                'warehouse_name',
                'warehouse_address',
                'proposal_code',
                'general_order_code',
                'issuer_id',
                'invoice_issuer_name',
                'invoice_confirm_name',
                'warehouse_id',
                'warehouse_type',
                'quantity',
                'title',
                'expected_date',
                'date_confirm',
                'is_batch',
                'reason_cancel',
                'description',
                'status',
                'created_at',
            ])->orderByRaw(" CASE status

            WHEN 'pending' THEN 1
            WHEN 'denied' THEN 2
            WHEN 'approved' THEN 3
            WHEN 'confirm' THEN 4
            ELSE 5
             END ASC
            ")->orderBy('expected_date', 'asc')
            ->when(!request()->user()->hasPermission('showroom.all'), function ($q) {
                $authUserId = request()->user()->id;
                $userHub = ShowroomUser::where('user_id', $authUserId)->pluck('showroom_id');
                if (request()->user()->hasPermission('showroom-proposal-issue.approve')) {
                    return $q->whereHas('warehouseIssue.showroom', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub);
                    });
                } else {
                    return $q->whereHas('warehouseIssue.showroom', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub)->where('issuer_id', request()->user()->id);
                    });
                }
            });

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('proposal_code')
                ->title('Mã phiếu')
                ->orderable(false)->width(50),
            Column::make('general_order_code')
                ->title('Mã đơn hàng')
                ->orderable(false)->width(50),
            Column::make('warehouse_name')
                ->title('Kho xuất'),
            Column::make('title')
                ->title('Tên phiếu')
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
        if ($this->hasPermission('showroom-proposal-issue.create') || $this->hasPermission('showroom.all')) {
            return $this->addCreateButton(route('showroom-proposal-issue.create'));
        }
        return [];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('showroom-proposal-issue.destroy'),
        ];
    }

    public function getBulkChanges(): array
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

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }

    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
}
