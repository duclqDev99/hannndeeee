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

class WarehouseMaterialTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(MaterialWarehouse::class)
            ->addActions([
                DetailAction::make()
                    ->route('warehouse-material.detail'),
                EditAction::make()
                    ->route('warehouse-material.edit'),

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


    public function getCheckboxColumnHeading(): array {
        return [];
    }
    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('warehouse-material.edit')->title('Tên kho'),
            Column::make('phone_number')->title('Số điện thoại'),
            Column::make('address')->title('Địa chỉ'),
            CreatedAtColumn::make()->title('Ngày tạo'),
            StatusColumn::make('status')->title('Trạng thái'),
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
