<?php

namespace Botble\CustomerBookOrder\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\CustomerBookOrder\Models\CustomerBookOrder;
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

class CustomerBookOrderTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(CustomerBookOrder::class)
            ->addActions([
                // EditAction::make()
                //     ->route('customer-book-order.edit'),
                DeleteAction::make()
                    ->route('customer-book-order.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('email', function (CustomerBookOrder $item) {
                if (! $this->hasPermission('customer-book-order.edit')) {
                    return BaseHelper::clean($item->email);
                }
                return Html::link(route('customer-book-order.edit', $item->getKey()), BaseHelper::clean($item->email));
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
                'username',
                'email',
                'phone',
                'address',
                'type_order',
                'note',
                'quantity',
                'image',
                'status',
                'expected_date',
                'created_at',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('username')
            ->title('Tên khách hàng'),
            Column::make('email'),
            Column::make('phone')
            ->title('Số điện thoại'),
            Column::make('address')
            ->title('Địa chỉ'),
            StatusColumn::make('type_order')
            ->title('Loại đặt hàng'),
            CreatedAtColumn::make('expected_date')
            ->title('Ngày cần hàng'),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        // return $this->addCreateButton(route('customer-book-order.create'), 'customer-book-order.create');
        return [];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('customer-book-order.destroy'),
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
