<?php

namespace Botble\SaleWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\SaleWarehouse\Models\SaleWarehouseChild;
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

class SaleWarehouseChildTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(SaleWarehouseChild::class)
            ->addActions([
                EditAction::make()
                    ->route('sale-warehouse-child.edit'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (SaleWarehouseChild $item) {
                if (!$this->hasPermission('sale-warehouse-child.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('sale-warehouse-child.edit', $item->getKey()), BaseHelper::clean($item->name));
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
                'sale_warehouse_id',
            ])
            ->with('saleWarehouse');
        if (!Auth::user()->hasPermission('sale-warehouse.all')) {
            $query->whereHas('saleWarehouse', function ($q) {
                $q->whereIn('id', get_list_sale_warehouse_id_for_current_user());
            });
        }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make(),
            Column::make('sale_warehouse.name')->title('Kho sale'),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('sale-warehouse-child.create'), 'sale-warehouse-child.create');
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
