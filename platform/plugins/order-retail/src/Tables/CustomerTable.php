<?php

namespace Botble\Sales\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sales\Models\Customer;
use Botble\Sales\Models\Sales;
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

class CustomerTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Customer::class)
            ->addActions([
                EditAction::make()
                    ->route('customer-purchase.edit'),
                DeleteAction::make()
                    ->route('customer-purchase.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Customer $item) {
                if (! $this->hasPermission('customer-purchase.edit')) {
                    return BaseHelper::clean($item->name);
                }
                return Html::link(route('customer-purchase.edit', $item->getKey()), BaseHelper::clean($item->name));
            })
            ->editColumn('level', function(Customer $item) {
                return BaseHelper::clean(trans('plugins/sales::sales.customer.level.'.  $item->level));
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
               'first_name',
               'last_name',
               'gender',
               'email',
               'phone',
               'address',
               'dob',
               'level',
               'created_at',
               'status',
           ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('last_name')
            ->title('Họ và tên đệm')
            ->orderable(false),
            Column::make('first_name')
            ->title('Tên')
            ->orderable(false),
            Column::make('gender')
            ->title('Giới tính')
            ->orderable(false),
            Column::make('email')
            ->title('Email')
            ->orderable(false),
            Column::make('phone')
            ->title('Số điện thoại')
            ->orderable(false),
            Column::make('level')
            ->title('Cấp độ')
            ->orderable(false),
            Column::make('last_name')
            ->orderable(false),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function getCheckboxColumnHeading(): array
    {
        return [];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('customer-purchase.create'), 'customer-purchase.create');
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('customer-purchase.destroy'),
        ];
    }

    public function getBulkChanges(): array
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

    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
