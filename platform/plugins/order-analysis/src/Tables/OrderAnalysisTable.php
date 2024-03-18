<?php

namespace Botble\OrderAnalysis\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\OrderAnalysis\Actions\ApproveAction;
use Botble\OrderAnalysis\Models\OrderAnalysis;
use Botble\OrderAnalysis\Enums\OrderAnalysisStatusEnum;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\LinkableColumn;
use Botble\Table\Columns\RowActionsColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\Columns\UpdatedAtColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class OrderAnalysisTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(OrderAnalysis::class)
            ->addActions([
                EditAction::make()
                    ->route('analyses.edit'),
                ApproveAction::make()
                    ->route('analyses.approve'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (OrderAnalysis $item) {
                if (! $this->hasPermission('analyses.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('analyses.edit', $item->id), BaseHelper::clean($item->name));
            })
            ->editColumn('created_by', function (OrderAnalysis $item) {
                return ($item->createdBy->username);
            })
            ->editColumn('updated_by', function (OrderAnalysis $item) {
                return ($item->updatedBy->username);
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
               'code',
               'description',
               'status',
               'created_by',
               'created_at',
               'updated_by',
               'updated_at',
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make(),
            Column::make('code'),
            Column::make('description'),
            StatusColumn::make('status'),
            Column::make('created_by'),
            CreatedAtColumn::make(),
            Column::make('updated_by'),
            UpdatedAtColumn::make('operation'),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('analyses.create'), 'analyses.create');
    }

    public function bulkActions(): array
    {
        return [
            // DeleteBulkAction::make()->permission('analyses.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            // 'name' => [
            //     'title' => trans('core/base::tables.name'),
            //     'type' => 'text',
            //     'validate' => 'required|max:100',
            // ],
            // 'code' => [
            //     'title' => trans('Mã'),
            //     'type' => 'text',
            //     'validate' => 'required|max:50',
            // ],
            // 'description' => [
            //     'title' => trans('Mô tả'),
            //     'type' => 'text',
            //     'validate' => 'required|max:120',
            // ],
            // 'created_at' => [
            //     'title' => trans('core/base::tables.created_at'),
            //     'type' => 'date',
            // ],
            // 'updated_at' => [
            //     'title' => trans('core/base::tables.updated_at'),
            //     'type' => 'date',
            // ],
        ];
    }

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
