<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Actions\DetailAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class WarehouseFinishedProductsTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(WarehouseFinishedProducts::class)
            ->addActions([
                DetailAction::make()
                    ->route('warehouse-finished-products.detail'),
                EditAction::make()
                    ->route('warehouse-finished-products.edit'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (WarehouseFinishedProducts $item) {
                if (!$this->hasPermission('warehouse-finished-products.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('warehouse-finished-products.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('action', function (WarehouseFinishedProducts $item) {
                $actionEdit = '
                <li><a data-bs-original-title="Edit" href="' . route('warehouse-finished-products.edit', $item->id) . '" class="dropdown-item">Chỉnh sửa</a></li>
                ';
                $detailBatch = '
                <li><a data-bs-original-title="Detail" href="' . route('warehouse-finished-products.detail', $item->id) . '" class="dropdown-item">Chi tiết lô</a></li>
                ';

                $detailOdd = '';
                if ($this->hasPermission('warehouse-finished-products.detail-odd')) {
                    $detailOdd = '
                    <li><a data-bs-original-title="Detail" href="' . route('warehouse-finished-products.detail-odd', $item->id) . '" class="dropdown-item">Chi tiết lẻ</a></li>
                    ';
                }
                // $detailWarehouse = '
                // <li><a data-bs-original-title="Detail" href="' . route('warehouse-finished-products.detail', $item->id) . '" class="dropdown-item">Chi tiết kho</a></li>
                // ';
                return '
                <div class="btn-group">
                <button type="button" class="btn btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                  <ul class="dropdown-menu">
                  ' . $detailBatch . '
                  ' . $actionEdit . '
                  ' . $detailOdd . '
                  </ul>
                </div>
                ';
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
            ])->when(!\Auth::user()->super_user,function($q){
                $q->whereHas('warehouseUsers', function ($query) {
                    $authUserId = \Auth::id();
                    $query->where('user_id', $authUserId);
                });
            });

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make()->route('warehouse-finished-products.edit'),
            CreatedAtColumn::make(),
            StatusColumn::make(),
            Column::make('action')->title('Tác vụ')->searchable(false)->orderable(false)->width(50),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('warehouse-finished-products.create'), 'warehouse-finished-products.create');
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
        return ['name' => [
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
            ],];
    }
}
