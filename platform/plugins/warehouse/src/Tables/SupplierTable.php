<?php

namespace Botble\Warehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Models\Supplier;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class SupplierTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Supplier::class)
            ->addActions([
                EditAction::make()
                    ->route('supplier.edit'),
                DeleteAction::make()
                    ->route('supplier.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Supplier $item) {
                if (! $this->hasPermission('supplier.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('supplier.edit', $item->id), BaseHelper::clean($item->name));
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
            Column::make('name')->title(trans('plugins/warehouse::supplier.name')),
            Column::make('phone_number')->title(trans('plugins/warehouse::supplier.contact_info')),
            Column::make('address')->title(trans('plugins/warehouse::supplier.address')),
            CreatedAtColumn::make()->title('Ngày tạo'),
            StatusColumn::make()->title('Trạng thái'),
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('supplier.create'), 'supplier.create');
    }

    public function bulkActions(): array
    {
        return [];

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
