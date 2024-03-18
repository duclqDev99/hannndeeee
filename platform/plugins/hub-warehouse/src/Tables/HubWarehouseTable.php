<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\HubWarehouse\Actions\DetailBatch;
use Botble\HubWarehouse\Actions\DetailHub;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class HubWarehouseTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(HubWarehouse::class)
            ->addActions([

                EditAction::make()
                    ->route('hub-warehouse.edit'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (HubWarehouse $item) {
                if (!$this->hasPermission('hub-warehouse.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('hub-warehouse.edit', $item->getKey()), BaseHelper::clean($item->name));
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
                'address',
                'phone_number',
                'created_at',
                'status',
            ])->when(!\Auth::user()->super_user && !\Auth::user()->hasPermission('hub-warehouse.all-permissions'), function ($q) {
                $q->whereHas('hubUsers', function ($query) {
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
            NameColumn::make()->route('hub-warehouse.edit'),
            Column::make('phone_number')->title('Số điện thoại'),
            Column::make('address')->title('Địa chỉ'),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function buttons(): array
    {
        if ($this->hasPermission('hub-warehouse.create') || $this->hasPermission('hub-warehouse.all-permissions')) {
            return $this->addCreateButton(route('hub-warehouse.create'));
        }
        return [];
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
                'title' => trans('Tên HUB'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => 'Trạng thái',
                'type' => 'select',
                'choices' => HubStatusEnum::labels(),
            ],
           
        ];
    }
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }

}
