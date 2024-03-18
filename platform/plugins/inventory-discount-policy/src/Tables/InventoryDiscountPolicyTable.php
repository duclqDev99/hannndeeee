<?php

namespace Botble\InventoryDiscountPolicy\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\InventoryDiscountPolicy\Models\InventoryDiscountPolicy;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class InventoryDiscountPolicyTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(InventoryDiscountPolicy::class)
            ->addActions([
                EditAction::make()
                    ->route('inventory-discount-policy.edit'),
                DeleteAction::make()
                    ->route('inventory-discount-policy.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('detail', function (InventoryDiscountPolicy $item) {
                return view('plugins/inventory-discount-policy::detail', compact('item'))->render();
            })
            ->editColumn('total_sale', function (InventoryDiscountPolicy $item) {
                if ($item->quantity === null) {
                    return number_format($item->quantity_done);
                }

                return sprintf('%d/%d', number_format($item->quantity_done), number_format($item->quantity));
            })

            ->editColumn('name', function (InventoryDiscountPolicy $item) {
                if (! $this->hasPermission('inventory-discount-policy.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('inventory-discount-policy.edit', $item->getKey()), BaseHelper::clean($item->name));
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
              '*'
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('detail')
                ->name('code')
                ->title(trans('Thông tin chi tiết'))
                ->alignStart(),
            Column::make('total_sale')
                ->title(trans('Tổng đã bán'))
                ->width(100),
            DateColumn::make('start_date')
                ->title(trans('Ngày bắt đầu')),
            DateColumn::make('end_date')
                ->title(trans('Ngày kết thúc')),
            StatusColumn::make()
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('inventory-discount-policy.create'), 'inventory-discount-policy.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('inventory-discount-policy.destroy'),
        ];
    }

    public function getBulkChanges(): array
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

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
