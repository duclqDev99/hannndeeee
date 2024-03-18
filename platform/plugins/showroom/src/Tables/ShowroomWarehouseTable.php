<?php

namespace Botble\Showroom\Tables;

use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\OrderReturnStatusEnum;
use Botble\Showroom\Actions\DetailAction;
use Botble\Showroom\Actions\DetailActionProduct;
use Botble\Showroom\Actions\DetailNotQrAction;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Yajra\DataTables\DataTables;

class ShowroomWarehouseTable extends TableAbstract
{
    protected $permission = [];

    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
        $this->permission['edit'] = auth()->user()->hasPermission('showroom-warehouse.edit');
        $this->defaultShowroomId = get_showroom_for_user()->pluck('id')->first();
    }

    public function setup(): void
    {
        $this
            ->model(ShowroomWarehouse::class)
            ->addActions([
                DetailAction::make()
                    ->route('showroom-warehouse.detail-batch')
                    ->permission('showroom-warehouse.index'),
                // DetailNotQrAction::make()s
                //     ->route('showroom-warehouse.detail-odd'),
                DetailActionProduct::make()
                    ->route('showroom-warehouse.detail-product')
                    ->permission('showroom-warehouse.index'),
                EditAction::make()
                    ->route('showroom-warehouse.edit'),

            ])->displayActionsAsDropdownWhenActionsMoresThan(0);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('showroom_id', function (ShowroomWarehouse $item) {
                if ($this->hasPermission('showroom.edit')) {
                    return Html::link(route('showroom.edit', $item->showroom_id), $item->showroom?->name);
                }
                return $item->showroom?->name;
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
                'showroom_id',
                'name',
                'description',
                'created_at',
                'status',
            ]);
        $filterWhereId = request()->query('filter_values')[0] ?? null;
        if ($filterWhereId === null) {
            $query->where('showroom_id', $this->defaultShowroomId);
        } else {
            $query->where('showroom_id', (int)$filterWhereId);
        }
//        if (!Auth::user()->hasPermission('showroom.all')) {
//            $query->whereHas('showroom', function ($q) {
//                $authUserId = Auth::id();
//                $q->whereHas('users', function ($query) use ($authUserId) {
//                    $query->where('user_id', $authUserId);
//                });
//            });
//        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $title_name = trans('plugins/showroom::showroom.column_name_table');
        return [
            IdColumn::make(),
            NameColumn::make()->route('showroom-warehouse.edit')->permission('showroom-warehouse.edit')->title($title_name['name']),
            Column::make('showroom_id')->title($title_name['showroom']),
            Column::make('description')->title($title_name['description']),
            CreatedAtColumn::make()->title($title_name['created_at']),
            StatusColumn::make()->title($title_name['status']),
        ];
    }

    public function buttons(): array
    {
        if ($this->hasPermission('showroom-warehouse.create') || $this->hasPermission('showroom.all')) {
            $buttons = $this->addCreateButton(route('showroom-warehouse.create'));
        }

        $listShowroomByUser = get_showroom_for_user()->pluck('name', 'id')->toArray();
        $route = route('showroom-warehouse.index');
        $defaultShowroomId = $this->defaultShowroomId;
        $selectHtml = Blade::render(view('plugins/showroom::reports.field.dropdown', compact('listShowroomByUser', 'route','defaultShowroomId'))->render());

        $buttons['selectField'] = [
            'class' => 'btn m-0 p-0 b',
            'text' => $selectHtml,
        ];
        return $buttons;
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
