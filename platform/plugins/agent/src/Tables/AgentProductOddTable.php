<?php

namespace Botble\Agent\Tables;

use Botble\Agent\Models\AgentProduct;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Ecommerce\Models\Product;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AgentProductOddTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(AgentProduct::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-receipt-products.edit'),
                DeleteAction::make()
                    ->route('proposal-receipt-products.destroy'),
            ])
            ->removeAllActions();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (AgentProduct $item) {
                return $item?->warehouse?->name . '-' . $item?->warehouse?->agent?->name;
            })

            ->editColumn('product.name', function (AgentProduct $item) {
                $product = $item?->product;
                foreach ($product->variationProductAttributes as $attribute) {
                    if ($attribute?->color) {

                        $color =  $attribute?->title;
                    }
                }
                foreach ($product->variationProductAttributes as $attribute) {
                    if (!$attribute?->color) {
                        $size =  $attribute?->title;
                    }
                }
                return $product->name . '<br> Màu: ' . $color . ' - Size: ' . $size;
            })
            ->editColumn('product.sku', function (AgentProduct $item) {

                return $item?->product?->sku;
            })
            ->editColumn('image', function (AgentProduct $item) {
                $imageTag = '';
                $img = $item?->product?->parentProduct->first()?->image;
                if ($img) {
                    $imageTag = '<img src="' . RvMedia::getImageUrl($img) . '" width="80" height="80" style="float: left; margin-right: 10px;"/>';
                } else {
                    $imageTag = '<img src="' . RvMedia::getDefaultImage() . '" width="80" height="80" style="float: left; margin-right: 10px;" />';
                }
                return $imageTag;
            })
            ->filter(function ($query) {
                if ($keyword = $this->request->input('search.value')) {
                    return $query
                        ->whereHas('product', function ($subQuery) use ($keyword) {
                            return $subQuery
                                ->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('sku', 'LIKE', '%' . $keyword . '%');
                        });
                }
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        if (isset(request()->input()['id'])) {
            $productStockId = request()->input()['id'];
        }
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'warehouse_id',
                'product_id',
                'quantity_qrcode',
                'quantity_qrcode_sold',
                'quantity_not_qrcode',
                'quantity_sold_not_qrcode',
                'where_id',
                'where_type',
                'quantity_qrcode_issue',
            ])->where('warehouse_id',$productStockId)->with('product')->orderBy('id');
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('product.name')->title('Tên sản phẩm')->orderable(false)
                ->searchable(true)->width(300),
            Column::make('image')->title('Hình ảnh')->orderable(false)
                ->searchable(false),
            Column::make('product.sku')->title('Mã sản phẩm')->orderable(false)
                ->searchable(true),
            Column::make('quantity_qrcode')
                ->title('Số lượng sản phẩm'),
        ];
    }

    public function buttons(): array
    {
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
