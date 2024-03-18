<?php

namespace Botble\Showroom\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Showroom\Models\Showroom;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkChanges\StatusBulkChange;
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
use Yajra\DataTables\DataTables;

class ShowroomTable extends TableAbstract
{

    protected $permission = [];

    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
        $this->permission['edit'] = auth()->user()->hasPermission('showroom.edit');
    }

    public function setup(): void
    {
        $this
            ->model(Showroom::class)
            ->addActions([
                EditAction::make()
                    ->route('showroom.edit'),
            ])->displayActionsAsDropdownWhenActionsMoresThan(0);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Showroom $item) {
                if (!$this->hasPermission('showroom.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('showroom.edit', $item->getKey()), BaseHelper::clean($item->name));
            })->editColumn('hub_id', function (Showroom $item) {
                return $item->hub ? $item->hub->name : '-';
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
                'phone_number',
                'hub_id',
                'created_at',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $title_name = trans('plugins/showroom::showroom.column_name_table');
        return [
            NameColumn::make()
                ->title($title_name['name'])
                ->route('showroom.edit')
                ->permission('showroom.edit')->permission('showroom.all'),
            Column::make('hub_id')->title('HUB'),
            Column::make('phone_number')->title($title_name['phone_number']),
            Column::make('description')->title($title_name['description']),
            CreatedAtColumn::make()->title($title_name['created_at']),
            StatusColumn::make()->title($title_name['status']),
        ];
    }

    public function buttons(): array
    {
        if ($this->hasPermission('showroom.create') || $this->hasPermission('showroom.all')) {
            return $this->addCreateButton(route('showroom.create'));
        }
        return [];
    }

    public function getBulkChanges(): array
    {
        $arrayBulkChanges = [];
        if ($this->permission['edit']) {
            $arrayBulkChanges[] = StatusBulkChange::make()->choices(BaseStatusEnum::labels());
        }

        return $arrayBulkChanges;
    }

    public function getFilters(): array
    {
        return [
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
