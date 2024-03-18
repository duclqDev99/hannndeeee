<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Table\Columns\Column;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class WarehouseFinishedProductOddTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(QuantityProductInStock::class)
            ->addActions([
                EditAction::make()
                ->route('agent-warehouse.edit'),
            ])->removeAllActions();
        ;

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product', function (QuantityProductInStock $item) {
                $attr = [];
                foreach ($item->product->variationProductAttributes as $attribute) {
                    $attr[] = $attribute->title;
                }
                return $item->product->name . ' - (' . implode(' , ', $attr) . ')';
            })
        ;

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        if (isset(request()->input()['id'])) {
            $wareHouseId = request()->input()['id'];
        }
        $query = $this->getModel()
            ->query()
            ->where('stock_id', $wareHouseId)
            ->select()
            ->with('product.variationProductAttributes')
            ;

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('product_id')
                ->title('ID')
                ->width(10),
            Column::make('product')
                ->orderable(false)
                ->searchable(true)
                ->title('Tên sản phẩm')
                ->width(900),
            // Column::make('quantity_not_qrcode')->title('Số lượng'),
            FormattedColumn::make('quantity_not_qrcode')
                    ->title(trans('Số lượng'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();
                        if (! $this->hasPermission('warehouse-finished-products.reduce-quantity')) {
                            return $item?->quantity_not_qrcode ?: 'sản phẩm đã hết';
                        }

                        return  $item?->quantity_not_qrcode  ? view('plugins/warehouse-finished-products::partials.quantity-not-qrcode', compact('item'))->render() : 'sản phẩm đã bán hết';
                    }),

            Column::make('quantity_sold_not_qrcode')
                ->orderable(true)
                ->searchable(true)
                ->title('Số lượng đã bán'),
            CreatedAtColumn::make()->title('Ngày tạo')
            ->width(200),

            // Column::make('name_product')->title('Tên sản phẩm'),
        ];
    }

    public function buttons(): array
    {
        if (isset(request()->input()['id'])) {
            $wareHouseId = request()->input()['id'];
            return $this->addCreateButton(route('warehouse-finished-products.create-product-manual',$wareHouseId), 'warehouse-finished-products.create-product-manual');
        }
        return [];
    }

    public function getCheckboxColumnHeading(): array
    {
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
        return [];
    }
}
