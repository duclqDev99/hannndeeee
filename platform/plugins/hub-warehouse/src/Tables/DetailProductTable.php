<?php

namespace Botble\HubWarehouse\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\HubWarehouse\Models\QuantityProductInStock;
use Botble\HubWarehouse\Models\Warehouse;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\WarehouseFinishedProducts\Enums\ProductBatchStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class DetailProductTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(QuantityProductInStock::class)
            ->addActions([
                EditAction::make()
                    ->route('hub-warehouse.edit'),
                DeleteAction::make()
                    ->route('hub-warehouse.destroy'),
            ])->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (QuantityProductInStock $item) {
                return $item?->warehouse?->name . '-' . $item?->warehouse?->hub?->name;
            })

            ->editColumn('product', function (QuantityProductInStock $item) {
                $product = $item?->product;
                $color = $product->variationProductAttributes[0]->title ?? '';
                $size = $product->variationProductAttributes[1]->title ?? '';
                return $item?->product?->name . '<br> Màu: ' . $color . ' - Size: ' . $size;
            })
            ->editColumn('sku', function (QuantityProductInStock $item) {
                return $item?->product?->sku;
            })
            ->editColumn('image', function (QuantityProductInStock $item) {
                $imageTag = '';
                $img = $item?->product?->parentProduct[0]?->image;
                if ($img) {
                    $imageTag = '<img src="' . RvMedia::getImageUrl($img) . '" width="80" height="80" style="float: left; margin-right: 10px;"/>';
                } else {
                    $imageTag = '<img src="' . RvMedia::getDefaultImage() . '" width="80" height="80" style="float: left; margin-right: 10px;" />';
                }
                return $imageTag;

            })

        ;


        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        if (isset(request()->input()['id'])) {
            $productStockId = request()->input()['id'];
        }
        $search = $this->request()->search['value'];
        $query = $this
            ->getModel()
            ->query()
            ->where('stock_id', $productStockId)
            ->select([
                'product_id',
                'stock_id',
                'quantity',
                'quantity_sold',
                'quantity_issue'
            ]);
        if ($search) {
            $query->whereHas(
                'product',
                function ($q) use ($search) {
                    $q->where('status', 'published')
                        ->where(function ($q) use ($search) {
                            return $q->where('name', 'LIKE', "%" . $search . "%")->orWhere('sku', 'LIKE', "%" . $search . "%");
                        });
                },
            );
        }


        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('name')->title('Kho')->orderable(false)->searchable(false),
            Column::make('product')->title('Tên sản phẩm')->orderable(false)
                ->searchable(false),
            Column::make('image')->title('Hình ảnh')->orderable(false)
                ->searchable(false),
            Column::make('sku')->title('Mã sản phẩm')->orderable(false)
                ->searchable(false),
            Column::make('quantity')
                ->title('Tổng số lượng trong kho')->className('text-center')->searchable(false)
            ,
            Column::make('quantity_issue')
                ->title('Tổng số lượng đã xuất kho')->className('text-center')->searchable(false)
            ,
        ];
    }

    public function buttons(): array
    {
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
        return $this->getBulkChanges();
    }
    public function getCheckboxColumnHeading(): array
    {
        return [];
    }

}
