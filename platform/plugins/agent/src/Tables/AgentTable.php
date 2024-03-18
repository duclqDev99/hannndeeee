<?php

namespace Botble\Agent\Tables;

use Botble\Agent\Models\Agent;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AgentTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Agent::class)
            ->addActions([
                EditAction::make()
                    ->route('agent.edit'),
            ])->displayActionsAsDropdownWhenActionsMoresThan(0);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('discount_value', function (Agent $item) {
                if ($item->discount_value == 0) {
                    return 'Không có';
                }
                return number_format($item->discount_value, 0, '.' , ',');
            })
            ->editColumn('discount_type', function (Agent $item) {
                if ($item->discount_value == 0) {
                    return 'Không có';
                }
                return $item->discount_type;
            })
        ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'description',
                'created_at',
                'status',
                'discount_value',
                'discount_type',
            ])->when(!\Auth::user()->super_user && !\Auth::user()->hasPermission('agent.all'), function ($q) {
                $q->whereHas('users', function ($query) {
                    $authUserId = \Auth::id();
                    $query->where('user_id', $authUserId);
                });
            });
        ;

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('agent.edit')->permission('agent.edit')->title('Tên'),
            Column::make('description')->title('Mô tả'),
            Column::make('discount_value')->title('Giá trị chiết khẩu'),
            Column::make('discount_type')->title('loại chiết khấu'),
            CreatedAtColumn::make()->title('Người tạo'),
            StatusColumn::make()->title('Trạng thái'),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('agent.create'), 'agent.create');
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
        return $this->getBulkChanges();
    }
}
