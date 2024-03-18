<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Actions\DetailBatch;
use Botble\HubWarehouse\Actions\DetailNotQrAction;
use Botble\HubWarehouse\Actions\DetailProduct;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\DataTables;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Blade;

class WarehouseTable extends TableAbstract
{
    protected $defaultHubId;
    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        parent::__construct($table, $urlGenerator);
        $this->defaultHubId = get_hub_for_user()->pluck('id')->first();
    }
    public function setup(): void
    {
        $this
            ->model(Warehouse::class)
            ->addActions([
                DetailBatch::make()
                    ->route('hub-stock.detail-batch')->permission('hub-stock.index'),
                DetailProduct::make()
                    ->route('hub-stock.detail-product')->permission('hub-stock.index'),
                // DetailNotQrAction::make()->route('hub-stock.detail-odd'),
                EditAction::make()->route('hub-stock.edit'),
            ])->displayActionsAsDropdownWhenActionsMoresThan(0);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Warehouse $item) {
                if (!$this->hasPermission('hub-stock.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('hub-stock.edit', $item->getKey()), BaseHelper::clean($item->name));
            })->editColumn('hub_name', function (Warehouse $item) {
                return $item->hub->name;
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
                'name',
                'created_at',
                'status',
                'hub_id'
            ]);
        if (!Auth::user()->hasPermission('hub-warehouse.all-permissions')) {
            $query->whereHas('hub', function ($query) {
                $authUserId = Auth::id();
                $query->whereHas('hubUsers', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                });
            });
        }
        $filterHubId = request()->query('filter_values')[0] ?? null;
        if ($filterHubId === null) {
            $query->where('hub_id', $this->defaultHubId);
        } else {
            $query->where('hub_id', (int)$filterHubId);
        }
        $query->orderBy('hub_id');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('hub-stock.edit'),
            Column::make('hub_name')->title('HUB')->orderable(false)->searchable(false),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        if ($this->hasPermission('hub-stock.create') || $this->hasPermission('hub-warehouse.all-permissions')) {
            $buttons = $this->addCreateButton(route('hub-stock.create'), 'hub-stock.create');
            $listHubByUser = get_hub_for_user()->pluck('name', 'id')->toArray();
            $route = route('hub-stock.index');
            $defaultHubId = $this->defaultHubId;

            $selectHtml = Blade::render(view('plugins/hub-warehouse::field.dropdown', compact('listHubByUser', 'route', 'defaultHubId'))->render());

            $buttons['selectField'] = [
                'class' => 'btn m-0 p-0 b',
                'text' => $selectHtml,
            ];
            return  $buttons;
        }

        return [];
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
        return [


        ];
    }
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }
}
