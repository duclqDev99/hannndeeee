<?php

namespace Botble\Agent\Tables;

use Botble\Agent\Models\AgentUser;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Agent\Models\AgentIssue;
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

class AgentIssueTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(AgentIssue::class)
            ->addActions([
                EditAction::make()
                    ->route('agent-issue.edit'),
                DeleteAction::make()
                    ->route('agent-issue.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (AgentIssue $item) {
                if (!$this->hasPermission('agent-issue.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('agent-issue.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('issue_code', function (AgentIssue $item) {
                return BaseHelper::clean(get_proposal_issue_product_code($item->issue_code));
            })
            ->editColumn('warehouse', function (AgentIssue $item) {
                return $item->warehouse->name . ' - ' . $item->warehouse->hub->name ;
            })
            ->editColumn('warehouse_name', function (AgentIssue $item) {
                return $item->warehouseIssue->name . ' - ' . $item->warehouseIssue->agent->name ;
            })
            ->editColumn('operator', function (AgentIssue $item) {
                return view('plugins/agent::actions.agent-issue',compact('item'))->render();
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
            ")->orderBy('expected_date', 'asc')
            ->when(!request()->user()->hasPermission('agent.all'), function ($q) {
                $authUserId = request()->user()->id;
                $userHub = AgentUser::where('user_id', $authUserId)->pluck('agent_id'); // Assuming you have a 'hub_id' column in HubUser
                if (request()->user()->hasPermission('agent-issue.confirm')) {
                    $q->whereHas('warehouseIssue.agent', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub);
                    });
                } else {
                    $q->whereHas('warehouseIssue.agent', function ($query) use ($userHub) {
                        $query->whereIn('id', $userHub)->where('issuer_id', request()->user()->id);
                        ;
                    });
                }

            });

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('issue_code')->title('Mã phiếu'),
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
        return $this->getBulkChanges();
    }
}
