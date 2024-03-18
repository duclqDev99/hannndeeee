<?php

namespace Botble\Agent\Tables;

use Botble\Agent\Actions\DetailAction;
use Botble\Agent\Actions\DetailNotQrAction;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Yajra\DataTables\DataTables;

class AgentWarehouseTable extends TableAbstract
{
    protected $defaultAgentId;
    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
        $this->defaultAgentId = get_agent_for_user()->pluck('id')->first();
    }
    public function setup(): void
    {
        $this
            ->model(AgentWarehouse::class)
            ->addActions([
                EditAction::make()
                    ->route('agent-warehouse.edit'),
                DetailAction::make()
                    ->route('agent-warehouse.detail-batch'),
                DetailNotQrAction::make()
                    ->route('agent-warehouse.detail-odd'),
            ])->displayActionsAsDropdownWhenActionsMoresThan(0);


    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('agent_id', function (AgentWarehouse $item) {
                if ($this->hasPermission('agent.edit')) {
                    return Html::link(route('agent.edit', $item->agent_id), $item->agent?->name);
                }
                return $item->agent?->name;
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
                'agent_id',
                'name',
                'description',
                'created_at',
                'status',
            ]);
        $filterAgentId = request()->query('filter_values') ?? null;
        if ($filterAgentId === null) {
            $query->where('agent_id', $this->defaultAgentId);
        } else {
            $query->where('agent_id', (int)$filterAgentId);
        }
        $query->orderBy('agent_id');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('agent-warehouse.edit')->permission('agent-warehouse.edit')->title('Tên'),
            Column::make('agent_id')->title('Đại lý'),
            Column::make('description')->title('Mô tả'),
            CreatedAtColumn::make()->title('Người tạo'),
            StatusColumn::make()->title('Trạng thái'),
        ];
    }

    public function buttons(): array
    {
        if(isset($this->defaultAgentId)){
            $buttons = $this->addCreateButton(route('agent-warehouse.create',['select_id'=> $this->defaultAgentId]), 'agent-warehouse.create');

            $listAgentByUser = get_agent_for_user()->pluck('name', 'id')->toArray();
            $route = route('agent-warehouse.index');
            $defaultAgentId = $this->defaultAgentId;

            $selectHtml = Blade::render(view('plugins/agent::field.dropdown', compact('listAgentByUser', 'route','defaultAgentId'))->render());

            $buttons['selectField'] = [
                'class' => 'btn m-0 p-0 b',
                'text' => $selectHtml,
            ];
        }

        return $buttons ?? [];
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
        return [];
    }

}
