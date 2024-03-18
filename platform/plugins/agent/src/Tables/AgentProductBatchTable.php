<?php

namespace Botble\Agent\Tables;

use Botble\ACL\Models\User;
use Botble\Agent\Models\AgentProductBatch;
use Botble\Agent\Models\AgentWarehouse;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Product;
use Botble\Media\Facades\RvMedia;
use Botble\ProductQrcode\Enums\QRStatusEnum;
use Botble\Warehouse\Enums\BaseStatusEnum;
use Botble\Table\Columns\Column;
use Botble\Warehouse\Actions\DetailAction;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialBatch;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Warehouse\Models\Supplier;
use Botble\WarehouseFinishedProducts\Enums\BatchDetailStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ProductBatch;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AgentProductBatchTable extends TableAbstract
{

    public function setup(): void
    {
        $this
            ->model(ProductBatch::class)
            ->addActions([
                EditAction::make()
                    ->route('proposal-receipt-products.edit'),
                DeleteAction::make()
                    ->route('proposal-receipt-products.destroy'),
            ])
            ->removeAllActions();;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product.name', function (ProductBatch $item) {
                $listDefaul = $item->listProduct;

                $totalQuantities = $listDefaul
                    ->where('status', BatchDetailStatusEnum::INBATCH)
                    ->groupBy('product_id')
                    ->map(function ($group) {
                        return $group->count();
                    });

                $productsInfo = $listDefaul->groupBy('product_id')
                    ->map(function ($group) {
                        return $group->count();
                    })->map(function ($quantity, $productId) use ($totalQuantities) {
                        $product = Product::find($productId);
                        $attr = [];

                        foreach ($product->variationProductAttributes as $attribute) {
                            $attr[] = $attribute->title;
                        }

                        $imageTag = '';

                        if ($product->parentProduct[0]->image) {
                            $imageTag = '<img src="' . RvMedia::getImageUrl($product->parentProduct[0]->image) . '" width="80" height="80" style="float: left; margin-right: 10px;"/>';
                        } else {
                            $imageTag = '<img src="' . RvMedia::getDefaultImage() . '" width="80" height="80" style="float: left; margin-right: 10px;" />';
                        }

                        $color = $product->variationProductAttributes[0]->title ?? '';
                        $size = $product->variationProductAttributes[1]->title ?? '';

                        $curQty = 0;

                        if (isset($totalQuantities[$productId])) {
                            $curQty = $totalQuantities[$productId];
                        }

                        $productInfo = '<div style="overflow: hidden;">' . $imageTag . '<div style="float: left;"><strong>' .
                            $product->name . '</strong><br>Mã: ' . $product->sku . '<br>Màu: ' . $color . ' Size: ' . $size .
                            '<br>Số lượng hiện tại: ' . $curQty . ' sản phẩm <br>Số lượng ban đầu: ' . $quantity . ' sản phẩm</div></div>';

                        return $productInfo;
                    });

                $result = $productsInfo->filter()->implode('<br>');
                return $result;
            })
            ->editColumn('total_quantity', function (ProductBatch $item) {

                return $item->quantity;
            })
            ->filter(function ($query) {
                if ($keyword = $this->request->input('search.value')) {
                    return $query
                        ->whereHas('listProduct.product', function ($subQuery) use ($keyword) {
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
                'receipt_id',
                'batch_code',
                'quantity',
                'start_qty',
                'status',
                'warehouse_id',
                'warehouse_type',
                'product_parent_id',
                'created_at'
            ])->with('product')
            ->where(function ($query) use ($productStockId) {
                $query->where('warehouse_type', AgentWarehouse::class)
                    ->where('warehouse_id', $productStockId);
            });
        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            Column::make('batch_code')
                ->title('Mã lô hàng'),
            Column::make('product.name')->title('Danh sách sản phẩm')->orderable(false)
                ->searchable(true),
            Column::make('start_qty')
                ->title('Tổng số lượng ban đầu')
                ->orderable(true)
                ->searchable(false)
                ->width(50),
            Column::make('quantity')
                ->title('Tổng số lượng hiện tại')
                ->orderable(true)
                ->searchable(false)
                ->width(50),
            CreatedAtColumn::make()->dateFormat('d-m-Y')
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
