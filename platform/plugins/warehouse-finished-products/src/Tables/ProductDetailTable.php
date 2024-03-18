<?php

namespace Botble\WarehouseFinishedProducts\Tables;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Actions\DetailAction;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\IdColumn;
use Botble\Warehouse\Models\QuantityMaterialStock;
use Botble\WarehouseFinishedProducts\Models\QuantityProductInStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ProductDetailTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(ProductVariation::class)
            ->addActions([
                DetailAction::make()
                    ->route('proposal-goods-issue.edit'),
            ])
            ->removeAllActions();
        ;

    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product_name', function (ProductVariation $item) {
                $attr = [];
                foreach ($item->variationItems as $attribute) {
                    $attr[] = $attribute->attribute->title;
                }
                return implode(', ', $attr);
            })
            ->editColumn('product_price', function (ProductVariation $item) {
                return $item->product->price;
            })
            ->editColumn('quantity', function (ProductVariation $item) {
                return $item->product->quantity;
            })
            ->editColumn('image', function (ProductVariation $item) {
                if ($item->product->image) {
                    return '<img src="' . RvMedia::getImageUrl($item->product->image) . '" width="80" height="80"/>';
                } else {
                    return '<img src = "https://phutungnhapkhauchinhhang.com/wp-content/uploads/2020/06/default-thumbnail.jpg" width="80" height="80" />';
                }
            })
            ->editColumn('product_stock', function (ProductVariation $item) {
                $listStock = [];
                foreach ($item->productStock as $stock) {
                    $listStock []=  $stock->warehouse->name .'| Số lượng: ' . $stock->quantity;
                }
                return implode(', ', $listStock);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $productStockId = get_material_stock_id_by_request();
        $query = $this
            ->getModel()
            ->query()
            ->select([
                    'id',
                    'product_id',
                    'configurable_product_id',
                    'is_default',
                ])->where('configurable_product_id', $productStockId);
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('product_name')->title('Màu, size')->orderable(false)->searchable(false),
            Column::make('image')->title('Hình ảnh')->orderable(false)->searchable(false),
            Column::make('product_stock')->title('Kho chứa')->orderable(false)->searchable(false),
            Column::make('quantity')->title('Số lượng')->orderable(false)->searchable(false),

        ];
    }


}
