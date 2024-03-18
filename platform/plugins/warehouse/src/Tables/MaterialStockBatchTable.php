<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Warehouse\Models\MaterialWarehouse;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Warehouse\Actions\DetailAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class MaterialStockBatchTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(MaterialWarehouse::class)
            ->addActions([
                DetailAction::make()
                    ->route('material-batch.detail'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (MaterialWarehouse $item) {
                if (! $this->hasPermission('warehouse-material.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('warehouse-material.edit', $item->getKey()), BaseHelper::clean($item->name));
            })->editColumn('quantity_batch', function (MaterialWarehouse $item) {
                return $item->countBatchInStock($item->id);
            })
            ->editColumn('quantity_material', function (MaterialWarehouse $item) {
                return $item->totalMaterialInStock($item->id);
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
               'phone_number',
               'address',
               'created_at',
               'status',
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('warehouse-material.edit')->title('Tên kho'),
            Column::make('phone_number')->title('Số điện thoại'),
            Column::make('quantity_batch')->title('Tổng số lô hàng')->searchable(false),
            Column::make('quantity_material')->title('Tổng nguyên phụ liệu')->searchable(false),
            CreatedAtColumn::make()->title('Ngày tạo')->searchable(false),
            StatusColumn::make()->title('Trạng thái')->searchable(false),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('warehouse-material.create'), 'warehouse-material.create');
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
    public function getCheckboxColumnHeading(): array {
        return [];
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
